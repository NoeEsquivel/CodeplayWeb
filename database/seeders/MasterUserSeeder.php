<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class MasterUserSeeder extends Seeder
{
    public function run()
    {
        // Verificar si el usuario Master ya existe
        $masterExists = Usuario::where('nombre', 'INED2025')->exists();

        if (!$masterExists) {
            Usuario::create([
                'nombre' => 'INED2025',
                'clave' => Hash::make('CodePlayenIned2025'),
                'rol' => 'Master',
                'puntos_totales' => 0,
                'puntos_usados' => 0,
            ]);
            
            $this->command->info('Usuario Master creado exitosamente!');
        } else {
            $this->command->info('El usuario Master ya existe.');
        }
    }
}