<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Reply;

class PostController extends Controller
{
    public function index()
    {
        try {
            // $posts = Post::with('comments')->get();
            $posts = Post::with(['comments.replies'])->get()->map(function ($post) {
                $post->imagen = url('storage/posts/' . $post->id . '/' . $post->imagen);
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
            $post = Post::findOrFail($id);
            $post->update($request->all());

            return response()->json([
                'message' => 'Post actualizado exitosamente',
                'post' => $post,
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
            $reply = Comment::findOrFail($replyId);
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
            $reply = Comment::findOrFail($replyId);
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

    public function show($id)
{
    try {
        $post = Post::with(['comments' => function($query) {
            $query->with(['replies'])->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $post->imagen = url('storage/posts/' . $post->id . '/' . $post->imagen);
        
        return response()->json([
            'message' => 'Post obtenido exitosamente',
            'post' => $post,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al obtener el post',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}