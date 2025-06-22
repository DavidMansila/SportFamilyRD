<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Deshabilitar completamente broadcasting en desarrollo
        if (app()->environment('local')) {
            return;
        }

        Broadcast::routes([
            'middleware' => ['web', 'auth:sanctum']
        ]);

        require base_path('routes/channels.php');
    }

    public function authenticate(Request $request)
    {
        return Broadcast::auth($request);
    }
}
