<?php

namespace App\Http\Controllers;

use App\Models\SavedNews;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SavedNewsController extends Controller
{
    public function toggleSave($newsId, Request $request)
    {
        $user = $request->user();

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
        $user = $request->user();

        $savedNewsIds = SavedNews::where('user_id', $user->id)
            ->pluck('news_id')
            ->toArray();

        return response()->json($savedNewsIds);
    }
}
