<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggleLike(Request $request)
    {
        $request->validate([
            'likeable_type' => 'required|in:post,comment,reply',
            'likeable_id' => 'required|integer',
        ]);

        $user = User::findOrFail($request['user_id']);
        if (!$user) {
            return response()->json(['message' => 'Debes iniciar sesión para dar like'], 401);
        }

        // Mapear tipo a modelo
        $modelMap = [
            'post' => Post::class,
            'comment' => Comment::class,
            'reply' => Reply::class,
        ];

        $modelClass = $modelMap[$request->likeable_type];
        $likeable = $modelClass::with('user')->find($request->likeable_id);

        if (!$likeable) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }

        // Verificar si ya le dio like
        $existingLike = Like::where('user_id', $user->id)
            ->where('likeable_type', $modelClass)
            ->where('likeable_id', $request->likeable_id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $isLiked = false;
        } else {
            $like = new Like([
                'user_id' => $user->id,
            ]);
            $likeable->likes()->save($like);
            $isLiked = true;
        }

        $newLikesCount = $likeable->likes()->count();
        $owner = $likeable->user;

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'likes_count' => $newLikesCount,
            'likeable_owner' => [
                'id' => $owner?->id,
                'name' => $owner?->name,
                'email' => $owner?->email,
            ],
        ]);
    }
}
