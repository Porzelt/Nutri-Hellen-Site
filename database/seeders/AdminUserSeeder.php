<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            $this->command?->warn('ADMIN_EMAIL/ADMIN_PASSWORD não definidos no .env: admin não criado.');

            return;
        }

        // Verifica se o usuário já existe para não duplicar se rodar de novo
        if (!User::where('email', $email)->exists()) {
            User::create([
                'name' => config('admin.name'),
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        }
    }
}
