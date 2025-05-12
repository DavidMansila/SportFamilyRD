<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Reply;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        try {
            // $posts = Post::with('comments')->get();
            $posts = Post::with(['comments.replies'])->get()->map(function ($post) {
                $post->imagen = $post->imagen 
                ? url('storage/posts/' . $post->id . '/' . $post->imagen)
                : url('public/imagenes/no_image.png');
                return $post;
            });
            
            return response()->json([
                'message' => 'Posts recibidos exitosamente',
                'posts' => $posts,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los posts',
                'error' => $e->getMessage(),
            ], 500);
        }
    
    }

    public function store(Request $request)
    {
        try {
            //  dd($request->all());
            $post = Post::create($request->all());

            if(isset($request['imagen']) && $request['imagen']){
                $imageName = Post::addImages($request['imagen'], $post->id);
                Post::where('id', $post->id)->update(['imagen' => $imageName]);
            }
            
            return response()->json([
                'message' => 'Post creado exitosamente',
                'posts' => $post,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener los posts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // dump($id);
            // dd($request->all());
            $post = Post::findOrFail($id);
            
            // Verificar autorización
           
    
            $data = $request->except('imagen');
            
            // Manejar nueva imagen
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($post->imagen) {
                    Storage::delete('posts/'.$post->id.'/'.$post->imagen);
                }
                
                $imageName = Post::addImages($request->file('imagen'), $post->id);
                $data['imagen'] = $imageName;
            }
    
            $post->update($data);
            $post->imagen = url('storage/posts/' . $post->id . '/' . $post->imagen);
    
            return response()->json([
                'message' => 'Post actualizado exitosamente',
                'post' => $post, // Incluir URL completa de la imagen
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    
    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            
            // Verificar autorización
            // if (auth()->user()->id !== $post->user_id) {
            //     return response()->json(['message' => 'No autorizado'], 403);
            // }
    
            // Eliminar directorio de imágenes
            Storage::deleteDirectory('posts/'.$post->id);
            
            $post->delete();
    
            return response()->json([
                'message' => 'Post eliminado exitosamente',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el post',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function createComment(Request $request)
    {
        try {
            $comment = Comment::create([
                'texto' => $request->texto,
                'post_id' => $request->post_id,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Comentario creado exitosamente',
                'comment' => $comment
            ], 201);
    
        } catch (\Exception $e) {
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
            $comment->update($request->all());

            return response()->json([
                'message' => 'Comentario actualizado exitosamente',
                'comment' => $comment,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el comentario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyComment($commentId)
    {
        try {
            $comment = Comment::findOrFail($commentId);
            $comment->delete();

            return response()->json([
                'message' => 'Comentario eliminado exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el comentario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReply($commentId)
    {
        try {
            //todo verificar si es necesaria esta funcion
            $comment = Comment::with('replies')->findOrFail($commentId);

            return response()->json([
                'message' => 'Respuestas obtenidas exitosamente',
                'replies' => $comment->replies,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las respuestas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createReply(Request $request, $commentId)
    {
        try {
            $reply = Reply::create([
                'texto' => $request->texto,
                'comment_id' => $commentId,
                'user_id' => auth()->id()
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Respuesta creada exitosamente',
                'comment' => $reply
            ], 201);
    
        } catch (\Exception $e) {
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
            $reply->update($request->all());

            return response()->json([
                'message' => 'Respuesta actualizada exitosamente',
                'reply' => $reply,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la respuesta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyReply($replyId)
    {
        try {
            $reply = Reply::findOrFail($replyId);
            $reply->delete();

            return response()->json([
                'message' => 'Respuesta eliminada exitosamente',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la respuesta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}