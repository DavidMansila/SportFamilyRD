<?php

namespace App\Http\Controllers;

use App\Models\SavedNews;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SavedNewsController extends Controller
{
    public function toggleSave($newsId, Request $request)
    {
        // Obtener usuario del request (adjuntado por el middleware)
        $user = $request->user;

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $news = News::findOrFail($newsId);

            $saved = SavedNews::where('user_id', $user->id)
                ->where('news_id', $newsId)
                ->first();

            if ($saved) {
                $saved->delete();
                return response()->json(['saved' => false]);
            } else {
                SavedNews::create([
                    'user_id' => $user->id,
                    'news_id' => $newsId
                ]);
                return response()->json(['saved' => true]);
            }
        } catch (\Exception $e) {
            Log::error('Error en toggleSave: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        // Obtener usuario del request
        $user = $request->user;

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $savedNewsIds = SavedNews::where('user_id', $user->id)
            ->pluck('news_id')
            ->toArray();

        return response()->json($savedNewsIds);
    }
}
