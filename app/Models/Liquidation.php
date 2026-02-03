<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'total_recaudacion',
        'total_gastos',
        'total_efectivo',
        'total_transferencia',
        'total_a_entregar',
        'clientes_visitados',
        'clientes_pagaron',
        'efectividad',
        'ventas_nuevas',
        'status',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_recaudacion' => 'decimal:2',
        'total_gastos' => 'decimal:2',
        'total_efectivo' => 'decimal:2',
        'total_transferencia' => 'decimal:2',
        'total_a_entregar' => 'decimal:2',
        'efectividad' => 'decimal:2',
        'ventas_nuevas' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
