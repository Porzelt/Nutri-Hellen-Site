<?php

namespace App\Livewire\Admin\Concerns;

use Illuminate\Support\Facades\Auth;

trait SaiDoSistema
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}
