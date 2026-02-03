<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'visit_date',
        'completed',
        'result',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
