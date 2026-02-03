<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\ProductReturn;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ReturnReport extends Component
{
    use WithPagination;

    // Filters
    public $startDate;
    public $endDate;
    public $vendedorId = '';
    public $productoId = '';
    public $condition = '';

    protected $queryString = [
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'vendedorId' => ['except' => ''],
        'productoId' => ['except' => ''],
        'condition' => ['except' => ''],
    ];

    public function mount()
    {
        if (!$this->startDate) {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->endDate) {
            $this->endDate = now()->format('Y-m-d');
        }
    }

    public function updating()
    {
        $this->resetPage();
    }

    public function getKpisProperty()
    {
        $query = ProductReturn::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay()
        ]);

        if ($this->vendedorId)
            $query->where('user_id', $this->vendedorId);
        if ($this->productoId)
            $query->where('product_id', $this->productoId);
        if ($this->condition)
            $query->where('product_condition', $this->condition);

        $totalRefunded = (float) $query->sum('refunded_amount');
        $recoveredUnits = (int) $query->where('product_condition', 'nuevo')->sum('quantity');

        // Return Rate Calculation
        $salesTotal = Sale::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay()
        ])->sum('total_amount');

        $returnRate = $salesTotal > 0 ? ($totalRefunded / $salesTotal) * 100 : 0;

        return [
            'total_refunded' => $totalRefunded,
            'recovered_units' => $recoveredUnits,
            'return_rate' => $returnRate,
        ];
    }

    public function exportCsv()
    {
        $query = ProductReturn::with(['sale.client', 'product', 'user'])
            ->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);

        if ($this->vendedorId)
            $query->where('user_id', $this->vendedorId);
        if ($this->productoId)
            $query->where('product_id', $this->productoId);
        if ($this->condition)
            $query->where('product_condition', $this->condition);

        $returns = $query->get();

        $filename = "Reporte_Devoluciones_" . now()->format('Y-m-d_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($returns) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Fecha', 'Cliente', 'Producto', 'Cantidad', 'Valor Reintegrado', 'Costo Unitario', 'Perdida de Margen', 'Estado/Condicion', 'Motivo', 'Responsable']);

            foreach ($returns as $ret) {
                // Calculation: Sale Price vs Cost Price
                $saleValue = $ret->refunded_amount;
                $costValue = ($ret->product->cost_price ?? 0) * $ret->quantity;
                $marginLoss = $ret->product_condition === 'dañado' ? $costValue : ($saleValue - $costValue);

                fputcsv($file, [
                    $ret->created_at->format('d/m/Y H:i'),
                    $ret->sale->client->name ?? 'N/A',
                    $ret->product->name ?? 'N/A',
                    $ret->quantity,
                    $ret->refunded_amount,
                    $ret->product->cost_price ?? 0,
                    $marginLoss,
                    strtoupper($ret->product_condition),
                    $ret->reason,
                    $ret->user->name ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $query = ProductReturn::with(['sale.client', 'product', 'user'])
            ->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);

        if ($this->vendedorId)
            $query->where('user_id', $this->vendedorId);
        if ($this->productoId)
            $query->where('product_id', $this->productoId);
        if ($this->condition)
            $query->where('product_condition', $this->condition);

        $returns = $query->orderBy('created_at', 'desc')->paginate(15);
        $vendedores = User::role('vendedor')->get();
        $productos = Product::orderBy('name')->get();

        return view('livewire.reports.return-report', [
            'returns' => $returns,
            'vendedores' => $vendedores,
            'productos' => $productos,
            'kpis' => $this->kpis
        ])->layout('layouts.app');
    }
}
