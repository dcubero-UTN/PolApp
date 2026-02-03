<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'invoice_number',
        'total_purchase',
        'status',
        'purchase_date',
        'due_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'total_purchase' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function getBalanceAttribute()
    {
        return $this->total_purchase - $this->payments()->sum('amount');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
