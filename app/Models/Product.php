<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'cost_price',
        'sale_price',
        'stock',
        'min_stock_alert',
        'image_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // We will handle dynamic hiding in toArray or a trait, but 
        // strictly speaking, we want to hide cost_price from JSON unless admin.
        // However, Laravel's $hidden is global. We'll use a specific method or accessor protection.
        // 'cost_price',
    ];

    /**
     * Get the profit attribute.
     */
    public function getProfitAttribute()
    {
        return $this->sale_price - $this->cost_price;
    }

    /**
     * Scope for low stock.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock_alert');
    }

    /**
     * Convert the model instance to an array.
     * Override to hide cost_price for non-admins.
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (auth()->check() && !auth()->user()->hasRole('admin')) {
            unset($array['cost_price']);
            unset($array['profit']); // Also hide derived profit if exposed
        }

        return $array;
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
