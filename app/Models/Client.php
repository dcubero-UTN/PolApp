<?php

namespace App\Models;

use App\Models\Scopes\SellerScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone_primary',
        'phone_secondary',
        'email',
        'address_details',
        'collection_day',
        'collection_frequency',
        'next_visit_date',
        'next_visit_notes',
        'hora_cobro',
        'latitude',
        'longitude',
        'current_balance',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new SellerScope);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function dailyVisits()
    {
        return $this->hasMany(DailyVisit::class);
    }

    public function getVisitedTodayAttribute()
    {
        return $this->dailyVisits()
            ->where('visit_date', date('Y-m-d'))
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('completed', true)
            ->exists();
    }

    /**
     * Scope a query to filter by collection day.
     */
    public function scopeForDay($query, $day)
    {
        if (!$day)
            return $query;
        return $query->where('collection_day', $day);
    }

    /**
     * Scope a query to search by name or phone.
     */
    public function scopeSearch($query, $term)
    {
        if (!$term)
            return $query;
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone_primary', 'like', "%{$term}%")
                ->orWhere('address_details', 'like', "%{$term}%");
        });
    }
}
