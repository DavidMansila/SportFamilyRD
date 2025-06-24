<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Obtener user_id del request
            $userId = $request->input('user_id');

            // Obtener posts con relaciones
            $posts = Post::with([
                'user',
                'likes',
                'comments' => function ($query) {
                    $query->withCount('likes as likes_count');
                    $query->with([
                        'user',
                        'replies' => function ($replyQuery) {
                            $replyQuery->withCount('likes as likes_count');
                            $replyQuery->with('user');
                        }
                    ]);
                }
            ])
                ->withCount('likes as likes_count')
                ->get();

            // Pre-cargar likes del usuario actual si se proporcionó user_id
            $userLikes = [];
            if ($userId) {
                $userLikes = Like::where('user_id', $userId)
                    ->get()
                    ->groupBy(['likeable_type', 'likeable_id']);
            }

            // Procesar los datos
            $posts = $posts->map(function ($post) use ($userId, $userLikes) {
                // Imagen del post
                if ($post->imagen) {
                    $post->imagen = url('storage/posts/' . $post->id . '/' . $post->imagen);
                } else {
                    $post->imagen = url('storage/posts/no_image.png');
                }

                // Verificar si el usuario dio like al post
                $post->is_liked = isset($userLikes[Post::class][$post->id]);

                // Procesar comentarios
                $post->comments->each(function ($comment) use ($userLikes) {
                    // Verificar si el usuario dio like al comentario
                    $comment->is_liked = isset($userLikes[Comment::class][$comment->id]);

                    // Procesar respuestas
                    $comment->replies->each(function ($reply) use ($userLikes) {
                        // Verificar si el usuario dio like a la respuesta
                        $reply->is_liked = isset($userLikes[Reply::class][$reply->id]);
                    });
                });

                return $post;
            });

            return response()->json([
                'message' => 'Posts recibidos exitosamente',
                'posts' => $posts,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching posts: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al obtener los posts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'contenido' => 'required|string',
                'categoria' => 'required|string',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $postData = $request->only(['titulo', 'contenido', 'categoria', 'user_id']);
            $post = Post::create($postData);

            if ($request->hasFile('imagen')) {
                $image = $request->file('imagen');
                $path = "posts/{$post->id}";

                if (!Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->makeDirectory($path);
                }

                $imageName = time() . '.' . $image->extension();
                $image->storeAs($path, $imageName, 'public');
                $post->imagen = $imageName;
                $post->save();
            }

            return response()->json([
                'message' => 'Post creado exitosamente',
                'post' => $post,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear el post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);

            // Validar campos
            $validator = Validator::make($request->all(), [
                'titulo' => 'sometimes|string|max:255',
                'contenido' => 'sometimes|string',
                'categoria' => 'sometimes|string',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si el usuario es el propietario del post
            if ($post->user_id != $request->input('user_id')) {
                return response()->json([
                    'message' => 'No autorizado para editar este post'
                ], 403);
            }

            $post->fill($request->only(['titulo', 'contenido', 'categoria']));

            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior
                if ($post->imagen) {
                    $oldImagePath = "posts/{$post->id}/{$post->imagen}";
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                }

                $image = $request->file('imagen');
                $imageName = time() . '.' . $image->extension();
                $image->storeAs("posts/{$post->id}", $imageName, 'public');
                $post->imagen = $imageName;
            }

            $post->save();

            return response()->json([
                'message' => 'Post actualizado exitosamente',
                'post' => $post,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating post: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {

            $post = Post::findOrFail($id);

            $input = $request->isMethod('delete')
                ? $request->json()->all()
                : $request->all();

            $validator = Validator::make($input, [
                'user_id' => 'required|exists:users,id',
                'user_type' => 'required|in:user,entrenador,admin'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si el usuario es el propietario o admin
            if ($post->user_id != $request->input('user_id') && $request->input('user_type') !== 'admin') {
                return response()->json([
                    'message' => 'No autorizado para eliminar este post'
                ], 403);
            }

            // Eliminar directorio de imágenes
            $directory = "posts/{$post->id}";
            if (Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }

            $post->delete();

            return response()->json([
                'message' => 'Post eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting post: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar el post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'texto' => 'required|string',
                'post_id' => 'required|exists:posts,id',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comment = Comment::create([
                'texto' => $request->texto,
                'post_id' => $request->post_id,
                'user_id' => $request->input('user_id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comentario creado exitosamente',
                'comment' => $comment
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating comment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear comentario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateComment(Request $request, $commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);

            // Validar campos
            $validator = Validator::make($request->all(), [
                'texto' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si el usuario es el propietario
            if ($comment->user_id != $request->input('user_id')) {
                return response()->json([
                    'message' => 'No autorizado para editar este comentario'
                ], 403);
            }

            $comment->texto = $request->texto;
            $comment->save();

            return response()->json([
                'message' => 'Comentario actualizado exitosamente',
                'comment' => $comment,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating comment: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar el comentario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyComment(Request $request, $commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);

            // Validar campos
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'user_type' => 'required|in:user,entrenador,admin'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si el usuario es el propietario o admin
            if ($comment->user_id != $request->input('user_id') && $request->input('user_type') !== 'admin') {
                return response()->json([
                    'message' => 'No autorizado para eliminar este comentario'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'message' => 'Comentario eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting comment: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar el comentario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createReply(Request $request, $commentId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'texto' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reply = Reply::create([
                'texto' => $request->texto,
                'comment_id' => $commentId,
                'user_id' => $request->input('user_id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta creada exitosamente',
                'reply' => $reply
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating reply: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear respuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateReply(Request $request, $replyId)
    {
        try {
            $reply = Reply::findOrFail($replyId);

            // Validar campos
            $validator = Validator::make($request->all(), [
                'texto' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si el usuario es el propietario
            if ($reply->user_id != $request->input('user_id')) {
                return response()->json([
                    'message' => 'No autorizado para editar esta respuesta'
                ], 403);
            }

            $reply->texto = $request->texto;
            $reply->save();

            return response()->json([
                'message' => 'Respuesta actualizada exitosamente',
                'reply' => $reply,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating reply: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar la respuesta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyReply(Request $request, $replyId)
    {
        try {
            $reply = Reply::findOrFail($replyId);

            // Validar campos
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'user_type' => 'required|in:user,entrenador,admin'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si el usuario es el propietario o admin
            if ($reply->user_id != $request->input('user_id') && $request->input('user_type') !== 'admin') {
                return response()->json([
                    'message' => 'No autorizado para eliminar esta respuesta'
                ], 403);
            }

            $reply->delete();

            return response()->json([
                'message' => 'Respuesta eliminada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting reply: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al eliminar la respuesta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
