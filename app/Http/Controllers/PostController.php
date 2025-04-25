<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        try {
            // $posts = Post::with('comments')->get();
            $posts = Post::with('comments')->get()->map(function ($post) {
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
    public function exampleFunction()
    {
        try {
            // $posts = Post::with('comments')->get();
            $posts = Post::all();

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
}
