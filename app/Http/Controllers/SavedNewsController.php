<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\SavedNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedNewsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $user->savedNews()
            ->with('news')
            ->get()
            ->pluck('news');
    }

    public function toggleSave($newsId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verificar si la noticia existe en la tabla correcta
        $newsExists = News::where('id', $newsId)->exists();

        if (!$newsExists) {
            return response()->json([
                'message' => 'La noticia ya no existe',
                'saved' => false
            ], 410);
        }

        $savedNews = SavedNews::where('user_id', $user->id)
            ->where('news_id', $newsId)
            ->first();

        if ($savedNews) {
            $savedNews->delete();
            return response()->json(['saved' => false]);
        }

        // Crear usando el nombre de tabla correcto
        SavedNews::create([
            'user_id' => $user->id,
            'news_id' => $newsId
        ]);

        return response()->json(['saved' => true]);
    }
}
