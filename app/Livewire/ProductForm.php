<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public $sku;
    public $name;
    public $description;
    public $cost_price;
    public $sale_price;
    public $stock;
    public $min_stock_alert = 5;
    public $image; // Temporary upload
    public $current_image_path; // View existing

    public function mount(?Product $product = null)
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Acceso denegado. Solo administradores.');
        }

        if ($product && $product->exists) {
            $this->product = $product;
            $this->fill($product->only([
                'sku',
                'name',
                'description',
                'cost_price',
                'sale_price',
                'stock',
                'min_stock_alert'
            ]));
            $this->current_image_path = $product->image_path;
        } else {
            $this->product = new Product();
            $this->stock = 0;
            $this->min_stock_alert = 5;
        }
    }

    public function rules()
    {
        return [
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($this->product->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0', // "no acepte números negativos"
            'min_stock_alert' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048', // 2MB Max
        ];
    }

    public function save()
    {
        // Permission check
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Solo el administrador puede gestionar productos.');
        }

        $this->validate();

        $data = [
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'min_stock_alert' => $this->min_stock_alert,
        ];

        // Only set stock for new products
        if (!$this->product->exists) {
            $data['stock'] = $this->stock;
        }

        if ($this->image) {
            $data['image_path'] = $this->image->store('products', 'public');
        }

        if ($this->product->exists) {
            $this->product->update($data);
        } else {
            Product::create($data);
        }

        session()->flash('message', 'Producto guardado correctamente.');
        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.product-form')->layout('layouts.app');
    }
}
