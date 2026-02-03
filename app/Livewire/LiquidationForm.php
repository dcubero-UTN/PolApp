<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Liquidation;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\DailyVisit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LiquidationForm extends Component
{
    public $liquidation;
    public $date;
    public $user_id;
    public $is_view_only = false;

    // Report Data
    public $payments = [];
    public $expenses = [];
    public $summary = [];
    public $kpis = [];

    public function mount($liquidation = null)
    {
        if ($liquidation) {
            $this->liquidation = Liquidation::findOrFail($liquidation);
            $this->date = $this->liquidation->date->format('Y-m-d');
            $this->user_id = $this->liquidation->user_id;
            $this->is_view_only = true;
            $this->loadPersistedData();
        } else {
            $this->date = now()->format('Y-m-d');
            $this->user_id = Auth::id();
            $this->calculateRealTimeData();
        }
    }

    public function calculateRealTimeData()
    {
        $date = $this->date;
        $userId = $this->user_id;

        // 1. Recaudación (Payments)
        $this->payments = Payment::with('sale.client')
            ->where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->whereNull('liquidation_id')
            ->get();

        // 2. Gastos (Approved Expenses)
        $this->expenses = Expense::where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('status', 'aprobado')
            ->whereNull('liquidation_id')
            ->get();

        // 3. Ventas Nuevas
        $ventasNuevasTotal = Sale::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->sum('total_amount');

        // 4. Totales por Método
        $recaudacionEfectivo = $this->payments->where('payment_method', 'efectivo')->sum('amount');
        $recaudacionTransfer = $this->payments->whereIn('payment_method', ['transferencia', 'sinpe'])->sum('amount');

        $gastosEfectivo = $this->expenses->where('payment_method', 'efectivo')->sum('amount');
        $gastosTransfer = $this->expenses->whereIn('payment_method', ['transferencia', 'sinpe'])->sum('amount');

        $totalRecaudacion = $recaudacionEfectivo + $recaudacionTransfer;
        $totalGastos = $gastosEfectivo + $gastosTransfer;
        $totalEfectivoNeto = $recaudacionEfectivo - $gastosEfectivo;

        $this->summary = [
            'total_recaudacion' => $totalRecaudacion,
            'total_gastos' => $totalGastos,
            'total_efectivo' => $recaudacionEfectivo,
            'total_transferencia' => $recaudacionTransfer,
            'total_a_entregar' => $totalEfectivoNeto // Efectivo físico a entregar
        ];

        // 5. KPIs
        $visitados = DailyVisit::where('user_id', $userId)
            ->whereDate('visit_date', $date)
            ->count();

        $pagaron = $this->payments->pluck('sale.client_id')->unique()->count();

        $this->kpis = [
            'clientes_visitados' => $visitados,
            'clientes_pagaron' => $pagaron,
            'efectividad' => $visitados > 0 ? ($pagaron / $visitados) * 100 : 0,
            'ventas_nuevas' => $ventasNuevasTotal
        ];
    }

    public function loadPersistedData()
    {
        $l = $this->liquidation;

        $this->summary = [
            'total_recaudacion' => $l->total_recaudacion,
            'total_gastos' => $l->total_gastos,
            'total_efectivo' => $l->total_efectivo,
            'total_transferencia' => $l->total_transferencia,
            'total_a_entregar' => $l->total_a_entregar
        ];

        $this->kpis = [
            'clientes_visitados' => $l->clientes_visitados,
            'clientes_pagaron' => $l->clientes_pagaron,
            'efectividad' => $l->efectividad,
            'ventas_nuevas' => $l->ventas_nuevas
        ];

        $this->payments = $l->payments()->with('sale.client')->get();
        $this->expenses = $l->expenses()->get();
    }

    public function save()
    {
        if ($this->is_view_only)
            return;

        $this->calculateRealTimeData();

        if ($this->payments->isEmpty() && $this->expenses->isEmpty()) {
            session()->flash('error', 'No hay movimientos para liquidar en esta fecha.');
            return;
        }

        DB::transaction(function () {
            $liquidation = Liquidation::create([
                'user_id' => $this->user_id,
                'date' => $this->date,
                'total_recaudacion' => $this->summary['total_recaudacion'],
                'total_gastos' => $this->summary['total_gastos'],
                'total_efectivo' => $this->summary['total_efectivo'],
                'total_transferencia' => $this->summary['total_transferencia'],
                'total_a_entregar' => $this->summary['total_a_entregar'],
                'clientes_visitados' => $this->kpis['clientes_visitados'],
                'clientes_pagaron' => $this->kpis['clientes_pagaron'],
                'efectividad' => $this->kpis['efectividad'],
                'ventas_nuevas' => $this->kpis['ventas_nuevas'],
                'status' => 'pendiente'
            ]);

            // Link payments and expenses
            Payment::whereIn('id', $this->payments->pluck('id'))
                ->update(['liquidation_id' => $liquidation->id]);

            Expense::whereIn('id', $this->expenses->pluck('id'))
                ->update(['liquidation_id' => $liquidation->id]);
        });

        session()->flash('message', 'Liquidación generada correctamente. Pendiente de confirmación por administrador.');
        return redirect()->route('liquidations.index');
    }

    public function confirmLiquidation()
    {
        if (!Auth::user()->hasRole('admin')) {
            session()->flash('error', 'Acceso denegado.');
            return;
        }

        $this->liquidation->update([
            'status' => 'confirmada',
            'confirmed_at' => now(),
            'confirmed_by' => Auth::id()
        ]);

        session()->flash('message', 'Liquidación confirmada exitosamente.');
        return redirect()->route('liquidations.index');
    }

    public function render()
    {
        return view('livewire.liquidation-form')->layout('layouts.app');
    }
}
