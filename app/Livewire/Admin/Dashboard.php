<?php

namespace App\Livewire\Admin;

use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    // Método para alternar o status (Contatado Sim/Não)
    public function toggleStatus($leadId)
    {
        $lead = Lead::find($leadId);
        if ($lead) {
            $lead->contatado = !$lead->contatado;
            $lead->save();
        }
    }

    // Método de Logout
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            // Busca todos os leads, ordenando pelos mais novos
            'leads' => Lead::latest()->get()
        ]);
    }
}