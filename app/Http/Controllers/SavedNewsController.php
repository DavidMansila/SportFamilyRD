<?php

namespace App\Http\Controllers;

use App\Models\SavedNews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedNewsController extends Controller
{
    public function toggleSave($newsId)
    {
        $user = Auth::user();
        $userId = $user ? $user->id : null;

        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $saved = SavedNews::where('user_id', $userId)
            ->where('news_id', $newsId)
            ->first();

        if ($saved) {
            $saved->delete();
            return response()->json(['saved' => false]);
        } else {
            SavedNews::create([
                'user_id' => $userId,
                'news_id' => $newsId
            ]);
            return response()->json(['saved' => true]);
        }
    }

    public function index(Request $request)
    {
        $userId = $request->header('X-User-ID');

        if (!$userId) {
            return response()->json(['message' => 'User ID required'], 400);
        }

        $savedNewsIds = SavedNews::where('user_id', $userId)
            ->pluck('news_id')
            ->toArray();

        return response()->json($savedNewsIds);
    }
}
