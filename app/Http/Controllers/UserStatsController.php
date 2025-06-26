<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Models\Training;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserStatsController extends Controller
{
    public function getStats($userId)
    {
        try {
            // 1. Total de publicaciones del usuario
            $postCount = Post::where('user_id', $userId)->count();

            // 2. Total de likes DADOS por el usuario (modificado)
            $likesCount = Like::where('user_id', $userId)->count();

            // 3. Inicializar variables
            $trainingRequests = 0;
            $trainerRequests = 0;

            $user = User::find($userId);

            if ($user) {
                if ($user->user_type === 'entrenador') {
                    // Solicitudes de usuarios para este entrenador
                    $trainingRequests = Training::where('trainer_id', $userId)->count();
                } else if ($user->user_type === 'admin') {
                    // TOTAL de solicitudes de entrenador
                    $trainerRequests = Trainer::count();
                }
            }

            return response()->json([
                'success' => true,
                'stats' => [
                    'posts' => $postCount,
                    'likes' => $likesCount,
                    'training_requests' => $trainingRequests,
                    'trainer_requests' => $trainerRequests,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getStats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getUserLikesCount($userId)
    {
        $likes = 0;

        // Likes en posts del usuario
        $likes += Like::where('likeable_type', Post::class)
            ->whereHas('likeable', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();

        // Likes en comentarios del usuario
        $likes += Like::where('likeable_type', Comment::class)
            ->whereHas('likeable', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();

        // Likes en respuestas del usuario
        $likes += Like::where('likeable_type', Reply::class)
            ->whereHas('likeable', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();

        return $likes;
    }
}
