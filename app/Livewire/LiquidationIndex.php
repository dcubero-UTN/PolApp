<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Liquidation;
use Illuminate\Support\Facades\Auth;

class LiquidationIndex extends Component
{
    public function render()
    {
        $query = Liquidation::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if (!Auth::user()->hasRole('admin')) {
            $query->where('user_id', Auth::id());
        }

        return view('livewire.liquidation-index', [
            'liquidations' => $query->paginate(10)
        ])->layout('layouts.app');
    }
}
