<?php

namespace App\Http\Controllers;

use App\Events\NewChat;
use App\Events\NewMessage;
use App\Events\MessageRead;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $chats = Chat::with([
            'user:id,name,image',
            'trainer.user:id,name,image',
            'lastMessage'
        ])
            // El contador de no leidos se calcula en la MISMA consulta. Antes
            // se resolvia dentro del map(), es decir, una consulta extra por
            // cada conversacion de la bandeja: el clasico N+1. Contra una base
            // remota como Supabase lo que se paga no es tanto la consulta como
            // la ida y vuelta, asi que una bandeja con 20 chats hacia 20 viajes
            // evitables cada vez que se abre.
            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->where('sender_id', '!=', $userId)->noLeidos();
            }])
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('trainer', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->where('status', 'accepted')
            ->get()
            ->map(function ($chat) use ($userId) {
                resolve_user_image($chat->user);
                if ($chat->trainer) {
                    resolve_user_image($chat->trainer->user);
                }

                return [
                    'id' => $chat->id,
                    'user_id' => $chat->user_id,
                    'trainer_id' => $chat->trainer_id,
                    'status' => $chat->status,
                    'unread_count' => (int) $chat->unread_count,
                    'last_message' => $chat->lastMessage ? [
                        'id' => $chat->lastMessage->id,
                        'message' => $chat->lastMessage->message,
                        'sender_id' => $chat->lastMessage->sender_id,
                        'created_at' => $chat->lastMessage->created_at
                    ] : null,
                    'user' => $chat->user,
                    // Guarda de nulo: tres lineas mas arriba ya se comprueba
                    // if ($chat->trainer) para resolver la imagen, pero aqui se
                    // accedia a ->id y ->user sin ella. Un chat huerfano (con el
                    // registro de entrenador borrado) tumbaba con un 500 el
                    // listado ENTERO de conversaciones del usuario.
                    'trainer' => $chat->trainer ? [
                        'id' => $chat->trainer->id,
                        'user' => $chat->trainer->user,
                    ] : null,
                ];
            });

        // Se quitaron dos Log::info que escribian el id de usuario y el numero
        // de conversaciones en CADA carga de la bandeja: ruido constante en el
        // log y datos de usuario sin motivo.
        return response()->json($chats);
    }



    public function storeMessage(Request $request, $chatId)
    {
        // max:1000 - sin tope, un solo mensaje podia ocupar megabytes en la
        // base remota Y difundirse por Pusher a los demas participantes.
        $request->validate(['message' => 'required|string|max:1000']);

        $user = $request->user();
        $chat = Chat::with('trainer')->findOrFail($chatId);

        // Solo alguno de los dos participantes del chat puede escribir en el.
        $esElAtleta = $chat->user_id == $user->id;
        $esElEntrenador = optional($chat->trainer)->user_id == $user->id;

        if (! $esElAtleta && ! $esElEntrenador) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // La conversacion tiene que estar abierta. Sin esto, un chat cerrado
        // -porque el entrenador rechazo la solicitud despues de haberla
        // aceptado- seguia admitiendo mensajes: desaparecia de las dos bandejas
        // (index solo lista los 'accepted') pero se podia seguir escribiendo en
        // el llamando al endpoint directamente.
        if ($chat->status !== 'accepted') {
            return response()->json([
                'message' => 'Esta conversación ya no está activa.',
            ], 403);
        }

        $message = Message::create([
            'chat_id' => $chatId,
            // El papel se deduce de la POSICION en este chat, no del user_type
            // global de la cuenta. Con el rol global, un entrenador que ademas
            // entrena con otro entrenador -es decir, que es el atleta de ESE
            // chat- enviaba sus mensajes marcados como 'trainer'. Y cuando a un
            // usuario se le aprueba la solicitud de entrenador, su user_type
            // cambia: los mensajes que mandara a partir de entonces en sus
            // conversaciones antiguas, donde sigue siendo el atleta, quedaban
            // marcados al reves que los anteriores, dentro del mismo hilo.
            'sender_id' => $user->id,
            'sender_type' => $esElAtleta ? 'user' : 'trainer',
            'message' => $request->message
        ]);

        // Al canal de la conversacion: lo escucha quien la tenga abierta.
        broadcast(new NewMessage($message))->toOthers();

        // Al canal personal del destinatario: le actualiza la lista de chats y
        // el contador de no leidos aunque no tenga esa conversacion abierta.
        broadcast(new NewChat($message));

        return response()->json($message);
    }



    public function markAsRead($chatId)
    {
        $userId = Auth::id();
        $chat = Chat::with('trainer')->findOrFail($chatId);

        $isParticipant = $chat->user_id == $userId
            || optional($chat->trainer)->user_id == $userId;
        if (!$isParticipant) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Message::booleano() y no true/false de PHP: en Postgres la columna es
        // boolean y Laravel enlaza los booleanos como enteros. Ver el modelo.
        Message::where('chat_id', $chatId)
            ->where('sender_id', '!=', $userId)
            ->noLeidos()
            ->update(['read' => Message::booleano(true)]);

        broadcast(new MessageRead($chatId))->toOthers();

        return response()->json(['success' => true]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'trainer_id' => 'required|exists:trainer,id',
        ]);

        $userId = $request->user()->id;

        // El chat NACE de una solicitud de entrenamiento aceptada: lo abre
        // TrainingController::update cuando el entrenador acepta. Este endpoint
        // creaba uno directamente, con 'status' => 'accepted' fijo y sin mirar
        // la tabla de solicitudes, asi que bastaba con conocer el trainer_id
        // -publico, sale en el directorio de entrenadores- para colarse en la
        // bandeja de cualquier entrenador y escribirle: sin solicitud, o
        // incluso despues de que la hubiera RECHAZADO.
        $solicitudAceptada = Training::where('user_id', $userId)
            ->where('trainer_id', $request->trainer_id)
            ->where('status', 'accepted')
            ->exists();

        if (! $solicitudAceptada) {
            return response()->json([
                'message' => 'Necesitas una solicitud de entrenamiento aceptada para abrir este chat.',
            ], 403);
        }

        // Verificar si ya existe un chat
        $existingChat = Chat::where('user_id', $userId)
            ->where('trainer_id', $request->trainer_id)
            ->first();

        if ($existingChat) {
            return response()->json([
                'message' => 'Ya existe un chat entre estos usuarios',
                'chat' => $existingChat
            ], 200);
        }

        $chat = Chat::create([
            'user_id' => $userId,
            'trainer_id' => $request->trainer_id,
            'status' => 'accepted'
        ]);

        return response()->json([
            'message' => 'Chat creado exitosamente',
            'chat' => $chat
        ], 201);
    }



    public function show(Request $request, $id)
    {
        $chat = Chat::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }, 'user', 'trainer'])
            ->findOrFail($id);

        // Solo alguno de los dos participantes puede leer la conversacion; si no,
        // cualquiera podria leer los mensajes privados de otros con solo
        // adivinar/incrementar el id del chat.
        $userId = $request->user()->id;
        $isParticipant = $chat->user_id == $userId
            || optional($chat->trainer)->user_id == $userId;
        if (!$isParticipant) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        Message::where('chat_id', $id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['read' => Message::booleano(true)]);

        return response()->json([
            'messages' => $chat->messages,
            'chat' => $chat
        ]);
    }



    // ELIMINADO: acceptChat(). Hacia Chat::findOrFail($id)->update(['status'
    // => 'accepted']) sin comprobar que quien llama participe en ese chat ni
    // que sea el entrenador, asi que habria bastado con recorrer los ids para
    // reabrir conversaciones ajenas. No estaba enrutado -era resto del flujo
    // antiguo en el que el chat se aceptaba aparte-, y hoy el estado lo
    // gobierna TrainingController al aceptar o rechazar la solicitud.
}
