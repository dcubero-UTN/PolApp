<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;

class CollectionAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'sale_id',
        'user_id',
        'reason',
        'notes',
        'latitude',
        'longitude',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
