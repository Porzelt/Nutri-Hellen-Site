<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Verifica se o usuário já existe para não duplicar se rodar de novo
        if (!User::where('email', 'henrique.porzeltneto@gmail.com')->exists()) {
            User::create([
                'name' => 'Hellen Nutricionista',
                'email' => 'henrique.porzeltneto@gmail.com',
                'password' => Hash::make('1234'), // Lembre-se de mudar isso em produção!
            ]);
        }
    }
}