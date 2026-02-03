<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\Purchase;
use Livewire\WithPagination;

class PurchaseIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $purchases = Purchase::with(['provider', 'items.product'])
            ->when($this->search, function ($query) {
                $query->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhereHas('provider', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.purchase-index', [
            'purchases' => $purchases,
        ])->layout('layouts.app');
    }
}
