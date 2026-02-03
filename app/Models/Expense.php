<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'place',
        'provider',
        'concept',
        'amount',
        'category',
        'payment_method',
        'justification',
        'attachment_path',
        'status',
        'reimbursed',
    ];

    protected $casts = [
        'date' => 'date',
        'reimbursed' => 'boolean',
        'amount' => 'decimal:2',
    ];

    /**
     * Scope to filter expenses by owner or show all for admin.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
