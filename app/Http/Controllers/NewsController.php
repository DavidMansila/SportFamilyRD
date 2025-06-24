<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $news = News::all();
            return response()->json([
                'message' => 'Noticias obtenidas con éxito',
                'news' => $news
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las noticias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'author' => 'required|string|max:255',
                'source' => 'required|string|max:255',
                'url' => 'required|url',
                'published_at' => 'nullable|date',
            ]);

            $news = News::create($request->all());

            return response()->json([
                'message' => 'Noticia creada exitosamente',
                'News' => $news
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la noticia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        return response()->json($news);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'author' => 'sometimes|required|string|max:255',
            'source' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url',
            'published_at' => 'nullable|date',
        ]);


        try {
            $news->update($request->all());
            return response()->json(
                [
                    'message' => 'Noticia actualizada con éxito',
                    'news' => $news
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la noticia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        try {
            $news->delete();
            return response()->json([
                'message' => 'Noticia eliminada con éxito'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la noticia',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function recentNews()
    {
        try {
            $news = News::select('id', 'title', 'content as excerpt', 'author', 'image', 'published_at as date', 'category')
                ->orderBy('published_at', 'desc')
                ->take(7)
                ->get();

            return response()->json($news);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener noticias recientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
