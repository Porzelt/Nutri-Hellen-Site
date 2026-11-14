<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\SaiDoSistema;
use App\Models\Lead;
use Livewire\Component;

class Dashboard extends Component
{
    use SaiDoSistema;

    // Método para alternar o status (Contatado Sim/Não)
    public function toggleStatus($leadId)
    {
        $lead = Lead::find($leadId);
        if ($lead) {
            $lead->contatado = !$lead->contatado;
            $lead->save();
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            // Busca todos os leads, ordenando pelos mais novos
            'leads' => Lead::latest()->get()
        ]);
    }
}
