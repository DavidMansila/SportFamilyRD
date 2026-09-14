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
            return error_json($e, 'Error al obtener las noticias', 500);
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
                'description' => 'required|string',
                'author' => 'required|string|max:255',
                'source' => 'required|string|max:255',
                'url' => 'required|url',
                'category' => 'required|string|max:50',
                'published_at' => 'nullable|date',
            ]);

            // Solo los campos validados, no $request->all(): este metodo no
            // esta enrutado hoy, pero create($request->all()) es la forma de
            // asignacion masiva que hay que evitar por defecto.
            $news = News::create($request->only([
                'title', 'description', 'author', 'source', 'url', 'category', 'published_at',
            ]));

            return response()->json([
                'message' => 'Noticia creada exitosamente',
                'News' => $news
            ], 200);
        } catch (\Exception $e) {
            return error_json($e, 'Error al crear la noticia', 500);
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

        // 'description', no 'content': la columna con el cuerpo del articulo en
        // la tabla NewsScrapping se llama 'description' (la vieja tabla 'news'
        // si tenia 'content', y de ahi venia la confusion).
        //
        // El efecto era silencioso y por eso paso desapercibido: 'content' no
        // esta en el $fillable del modelo, asi que update() lo descartaba sin
        // avisar, la peticion respondia 200 "Noticia actualizada con exito" y el
        // texto no cambiaba. Ademas obligaba a mandar un campo 'content' que no
        // existe solo para pasar la validacion.
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'author' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'published_at' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $news = News::findOrFail($id);

        $data = $request->only([
            'title',
            'description',
            'author',
            'category',
            'published_at'
        ]);

        if ($request->hasFile('image')) {
            // Disco 'public' en las tres operaciones. Antes se ESCRIBIA en
            // 'public' pero se comprobaba y borraba con Storage::exists() /
            // Storage::delete(), que van al disco por defecto ('local'): la
            // condicion nunca se cumplia y las imagenes viejas no se borraban
            // nunca. Con el disco publico apuntando a Supabase Storage en
            // produccion, el desajuste es total.
            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
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

            // Eliminar imagen asociada (disco 'public', que es donde se escribe)
            if ($news->image && Storage::disk('public')->exists($news->image)) {
                Storage::disk('public')->delete($news->image);
            }

            $news->delete();

            return response()->json([
                'message' => 'Noticia eliminada con éxito'
            ], 200);
        } catch (\Exception $e) {
            return error_json($e, 'Error al eliminar la noticia', 500);
        }
    }


    public function recentNews()
    {
        try {
            // 'description', no 'content': la columna 'content' no existe en
            // NewsScrapping y esta consulta fallaba con un error de SQL.
            $news = News::select(
                'id',
                'title',
                'description as excerpt',
                'author',
                'image',
                'published_at as date',
                'category')
                ->orderBy('published_at', 'desc')
                ->take(7)
                ->get();

            return response()->json($news);
        } catch (\Exception $e) {
            return error_json($e, 'Error al obtener noticias recientes', 500);
        }
    }
}
