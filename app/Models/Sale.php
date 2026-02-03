<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'total_amount',
        'initial_downpayment',
        'current_balance',
        'status',
        'number_of_installments',
        'suggested_quota',
        'quota_period',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns()
    {
        return $this->hasMany(\App\Models\ProductReturn::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function collectionAttempts()
    {
        return $this->hasMany(CollectionAttempt::class);
    }
}
