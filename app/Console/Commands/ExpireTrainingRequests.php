<?php

namespace App\Console\Commands;

use App\Mail\NuevaSolicitudExpirada;
use Illuminate\Console\Command;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ExpireTrainingRequests extends Command
{
    protected $signature = 'training:expire';
    protected $description = 'Marca solicitudes de entrenamiento pendientes como expiradas después de 3 días laborables';

    public function handle()
    {
        $cutoffDate = Carbon::now()->subWeekdays(3);

        $updatedCount = Training::where('status', 'pending')
            ->where('created_at', '<', $cutoffDate)
            ->update(['status' => 'expired']);

        if ($updatedCount > 0) {
            $trainings = Training::where('status', 'expired')
                ->where('created_at', '<', $cutoffDate)
                ->get();    
            foreach ($trainings as $training) {
                $entrenador = $training->trainer;
                $usuario = $training->user;
                Mail::to($usuario->email)->send(new NuevaSolicitudExpirada($entrenador, $usuario));
            }
        }
       
        $this->info('Solicitudes expiradas actualizadas: ' . $updatedCount);
    }
}
