<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function getCart(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with(['items.product', 'items.event'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json(['items' => []]);
        }

        return response()->json([
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'item' => $item->item_type === 'product'
                        ? $item->product
                        : $item->event,
                    'type' => $item->item_type
                ];
            })
        ]);
    }


    public function updateItem(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . self::MAX_CANTIDAD,
        ]);

        // El item debe pertenecer al carrito del usuario autenticado; si no,
        // cualquiera podria modificar la cantidad de items del carrito de otro
        // usuario con solo adivinar/incrementar el id.
        if ($item->cart->user_id != $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Mismo control de existencias que al añadir: sin esto bastaba con
        // añadir una unidad y despues subir la cantidad por esta otra puerta.
        $modelo = $item->item_type === 'product' ? \App\Models\Product::class : \App\Models\Calendar::class;
        $articulo = $modelo::find($item->item_id);

        if ($articulo) {
            $disponible = $this->existenciasDe($articulo, $item->item_type);

            if ($disponible !== null && $request->quantity > $disponible) {
                return response()->json([
                    'message' => $disponible > 0
                        ? "Solo quedan {$disponible} disponibles."
                        : 'Este artículo está agotado.',
                    'disponible' => $disponible,
                ], 422);
            }
        }

        $item->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Item updated']);
    }

    public function removeItem(Request $request, CartItem $item)
    {
        if ($item->cart->user_id != $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $item->delete();
        return response()->json(['message' => 'Item removed']);
    }


    /** Tope por linea del carrito. */
    private const MAX_CANTIDAD = 99;

    public function addItem(Request $request)
    {
        $datos = $request->validate([
            'item_type' => 'required|in:product,event',
            'item_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:' . self::MAX_CANTIDAD,
        ]);

        // El articulo tiene que EXISTIR. Antes 'item_id' solo se validaba como
        // entero, asi que se podian meter en el carrito referencias a productos
        // o eventos inexistentes: getCart() los devolvia como null y la vista
        // del carrito quedaba con huecos.
        //
        // La tabla depende del tipo, asi que no sirve una regla exists: fija.
        $modelo = $datos['item_type'] === 'product' ? \App\Models\Product::class : \App\Models\Calendar::class;
        $articulo = $modelo::find($datos['item_id']);

        if (! $articulo) {
            return response()->json(['message' => 'El artículo no existe'], 422);
        }

        $user = $request->user();

        // TODO EL BLOQUE va en una transaccion. Entre el firstOrCreate del
        // carrito y la escritura de la linea hay dos lecturas de las que el
        // codigo depende, y sin transaccion dos peticiones simultaneas -dos
        // pulsaciones rapidas en "añadir", que es justo lo que pasa en la
        // practica- se pisaban: creaban dos carritos activos para la misma
        // persona (getCart solo muestra uno, asi que parte de lo añadido
        // desaparecia de la vista), o dos lineas del mismo articulo, cada una
        // con su propio tope de 99, burlando el limite por duplicado.
        return DB::transaction(function () use ($user, $datos, $articulo) {
            $cart = Cart::firstOrCreate([
                'user_id' => $user->id,
                'status' => 'active'
            ]);

            $existingItem = $cart->items()
                ->where('item_type', $datos['item_type'])
                ->where('item_id', $datos['item_id'])
                ->lockForUpdate()
                ->first();

            $cantidadActual = $existingItem ? $existingItem->quantity : 0;

            // La SUMA tambien se acota: la validacion de arriba solo mira la
            // cantidad de ESTA peticion, asi que sin este min() se podia llegar
            // a cualquier numero repitiendo la llamada.
            $cantidadPedida = min(
                $cantidadActual + ($datos['quantity'] ?? 1),
                self::MAX_CANTIDAD
            );

            // No se puede pedir mas de lo que hay. El carrito no miraba las
            // existencias en ningun momento -ni al añadir ni al cambiar la
            // cantidad-, asi que se podian reservar 99 unidades de un articulo
            // con 4 en almacen, o mas entradas de un evento que aforo tiene.
            $disponible = $this->existenciasDe($articulo, $datos['item_type']);

            if ($disponible !== null && $cantidadPedida > $disponible) {
                return response()->json([
                    'message' => $disponible > 0
                        ? "Solo quedan {$disponible} disponibles."
                        : 'Este artículo está agotado.',
                    'disponible' => $disponible,
                ], 422);
            }

            if ($existingItem) {
                $existingItem->update(['quantity' => $cantidadPedida]);
            } else {
                $cart->items()->create([
                    'item_type' => $datos['item_type'],
                    'item_id' => $datos['item_id'],
                    'quantity' => $cantidadPedida,
                ]);
            }

            return $this->respuestaDelCarrito($cart);
        });
    }

    /**
     * Existencias disponibles de un articulo, o null si no se controlan.
     *
     * Los productos llevan 'stock'; los eventos, 'quantity' como aforo.
     */
    private function existenciasDe($articulo, string $tipo): ?int
    {
        $columna = $tipo === 'product' ? 'stock' : 'quantity';

        return isset($articulo->{$columna}) ? (int) $articulo->{$columna} : null;
    }

    private function respuestaDelCarrito(Cart $cart)
    {

        // Cargar los productos nuevamente
        $cart->load('items.product');

        return response()->json([
            'message' => 'Item added to cart',
            'cart' => $cart
        ]);
    }


    public function clearCart(Request $request)
    {
        $user = $request->user();

        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}
