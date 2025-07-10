<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Training;
use Carbon\Carbon;

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

        $this->info('Solicitudes expiradas actualizadas: ' . $updatedCount);
    }
}
