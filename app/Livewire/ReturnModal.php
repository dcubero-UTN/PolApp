<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Actions\ProcessProductReturn;

class ReturnModal extends Component
{
    public $showModal = false;
    public $showConfirmModal = false;
    public $sale; // Keep this property as it's used
    public $saleId = null; // Add this property
    public $selectedProduct = null;
    public $quantity = 1;
    public $product_condition = 'nuevo';
    public $reason = '';

    protected $listeners = ['openReturnModal'];

    public function openReturnModal($saleId)
    {
        $this->reset(['selectedProduct', 'quantity', 'product_condition', 'reason']);
        $this->sale = Sale::with('items.product', 'client')->findOrFail($saleId);
        $this->saleId = $saleId; // Set saleId when opening the modal

        if ($this->sale->items->isEmpty()) {
            session()->flash('error', 'Esta venta no tiene productos para devolver.');
            return;
        }

        $this->showModal = true;
    }

    public function updatedSelectedProduct($saleItemId)
    {
        if ($saleItemId) {
            $saleItem = $this->sale->items->firstWhere('id', $saleItemId);
            $this->quantity = min(1, $saleItem->quantity ?? 1);
        }
    }

    public function confirmReturn()
    {
        $this->validate([
            'selectedProduct' => 'required',
            'quantity' => 'required|numeric|min:1',
            'product_condition' => 'required'
        ]);
        $this->showConfirmModal = true;
    }

    public function cancelConfirm()
    {
        $this->showConfirmModal = false;
    }

    public function processReturn()
    {
        // Validations again for security
        $this->validate([
            'selectedProduct' => 'required',
            'quantity' => 'required|numeric|min:1',
            'product_condition' => 'required',
            'reason' => 'nullable|string|max:500', // Keep original reason validation
        ]);

        // The sale object is already loaded in $this->sale from openReturnModal
        // $sale = \App\Models\Sale::findOrFail($this->saleId); // This line is redundant if $this->sale is already set

        try {
            $saleItem = $this->sale->items->firstWhere('id', $this->selectedProduct);

            $action = new ProcessProductReturn();
            $action->execute([
                'sale_id' => $this->sale->id,
                'product_id' => $saleItem->product_id,
                'quantity' => $this->quantity,
                'product_condition' => $this->product_condition,
                'reason' => $this->reason,
            ]);

            session()->flash('message', 'Devolución procesada correctamente.');
            $this->showModal = false;
            $this->dispatch('return-processed');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.return-modal');
    }
}
