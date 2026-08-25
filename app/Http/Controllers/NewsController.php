<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
    public function update(Request $request, $id)
    {
        if ($request->user()->user_type !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'published_at' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $news = News::findOrFail($id);

        $data = $request->only([
            'title',
            'content',
            'author',
            'category',
            'published_at'
        ]);

        if ($request->hasFile('image')) {
            if ($news->image && Storage::exists($news->image)) {
                Storage::delete($news->image);
            }

            $imagePath = $request->file('image')->store('news_images', 'public');
            $data['image'] = $imagePath;
        }

        $news->update($data);

        return response()->json([
            'message' => 'Noticia actualizada con éxito',
            'news' => $news
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            if ($request->user()->user_type !== 'admin') {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            $news = News::findOrFail($id);

            // Eliminar imagen asociada
            if ($news->image && Storage::exists($news->image)) {
                Storage::delete($news->image);
            }

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
            $news = News::select(
                'id', 
                'title', 
                'content as excerpt', 
                'author',
                'image', 
                'published_at as date', 
                'category')
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
