<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sale;
use App\Models\User;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'user_id',
        'amount',
        'balance_before',
        'balance_after',
        'payment_method',
        'reference_number',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
