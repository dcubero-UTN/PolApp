<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $showDeleteModal = false;
    public $productIdToDelete = null;

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->productIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->productIdToDelete = null;
    }

    public function deleteConfirmed()
    {
        if (!$this->productIdToDelete)
            return;

        if (Auth::user()->hasRole('admin')) {
            $product = Product::find($this->productIdToDelete);
            if ($product) {
                // Check if product has sales or purchases...
                if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
                    session()->flash('error', 'No se puede eliminar un producto con historial de ventas o compras. Considere inactivarlo.');
                } else {
                    $product->delete();
                    session()->flash('message', 'Producto eliminado.');
                }
            }
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $query = Product::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            });

        // Calculate total inventory value (Cost * Stock) - Admin Only
        $totalValue = 0;
        if (Auth::user()->hasRole('admin')) {
            $totalValue = Product::sum(\Illuminate\Support\Facades\DB::raw('cost_price * stock'));
        }

        return view('livewire.product-index', [
            'products' => $query->orderBy('name')->paginate(10),
            'totalValue' => $totalValue,
        ])->layout('layouts.app');
    }
}
