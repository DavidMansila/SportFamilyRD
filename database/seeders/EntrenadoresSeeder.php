<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EntrenadoresSeeder extends Seeder
{
    public function run()
    {
        $names = [
            'Carlos Gómez', 'María Rodríguez', 'Juan Pérez', 'Ana Martínez', 'Luis Fernández',
            'Sofía Ramírez', 'Miguel Torres', 'Lucía Morales', 'Pedro Herrera', 'Valentina Castro',
            'Javier Ruiz', 'Camila Mendoza', 'Andrés Silva', 'Paula Ortega', 'Diego Vargas'
        ];

        for ($i = 0; $i < 15; $i++) {
            User::create([
                'name' => $names[$i],
                'email' => 'entrenador' . (21 + $i) . '@gmail.com',
                'password' => Hash::make('12345678'),
                'user_type' => 'user',
            ]);
        }
    }
}