<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use App\Models\ProductReturn;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesProfitabilityReport extends Component
{
    // Filters
    public $startDate;
    public $endDate;
    public $viewMode = 'daily'; // daily, monthly

    // Comparison
    public $previousPeriodStart;
    public $previousPeriodEnd;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->updateComparisonDates();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['startDate', 'endDate'])) {
            $this->updateComparisonDates();
        }
    }

    protected function updateComparisonDates()
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        // Compare with previous month relative to start date
        $this->previousPeriodStart = $start->copy()->subMonth()->format('Y-m-d');
        $this->previousPeriodEnd = $end->copy()->subMonth()->format('Y-m-d');
    }

    public function getStatsProperty()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // 1. Gross Sales
        $sales = Sale::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelado')
            ->get();

        $grossSales = $sales->sum('total_amount');

        // 2. Returns and COGS
        // We need detailed items to calculate proper COGS
        // Note: For returns, we should subtract the sale value from Net Sales, 
        // AND subtract the Cost from COGS (because we got the product back)

        $saleItems = SaleItem::whereHas('sale', function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end])
                ->where('status', '!=', 'cancelado');
        })->get();

        // Initial COGS based on all sales
        $initialCogs = $saleItems->sum(function ($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });

        // Calculate Returns Value and Returned Cost
        $returns = ProductReturn::whereBetween('created_at', [$start, $end])->get();
        $totalReturnsValue = $returns->sum('refunded_amount');

        // For returned cost, we need to know the cost of the specific item returned.
        // ProductReturn has product_id, we can look up current cost or try to link to sale item.
        // For simplicity and robustness given current models, using current product cost or average
        // is acceptable if unit_cost wasn't stored on return, but better to check if we can link.
        // ProductReturn is linked to Sale. We can approximate using the product relation.
        $totalReturnedCost = $returns->sum(function ($ret) {
            // Assuming cost hasn't fluctuated wildly or using current cost as proxy for returned value to inventory
            return $ret->quantity * ($ret->product->cost_price ?? 0);
        });

        // Net Sales = Gross Sales - Returns Value
        $netSales = $grossSales - $totalReturnsValue;

        // Actual COGS = Initial COGS - Cost of Returned Goods (since we have them back in stock/merma logic handled separately? 
        // Usually, if it's damaged (merma), it's a loss (Expense or separate line). 
        // If it's new (stock), it reduces COGS.
        // Let's assume for Net Profit: Net Sales - (COGS - ReturnedCost_GoodCondition) - Expenses - Cost_BadCondition

        // Simplified approach per request:
        // Utilidad Bruta: Ventas Netas - Costo de Ventas.
        // Costo de Ventas here implies Cost of Goods *Sold* (and kept by customer).
        // So yes, subtract cost of returned items from total COGS.
        $actualCogs = $initialCogs - $totalReturnedCost;

        $grossProfit = $netSales - $actualCogs;

        // 3. Operating Expenses
        $expenses = Expense::whereBetween('date', [$start, $end])
            ->where('status', 'aprobado')
            ->sum('amount');

        // 4. Net Profit
        $netProfit = $grossProfit - $expenses;

        // Margins
        $grossMargin = $netSales > 0 ? ($grossProfit / $netSales) * 100 : 0;
        $netMargin = $netSales > 0 ? ($netProfit / $netSales) * 100 : 0;

        return [
            'gross_sales' => $grossSales,
            'returns' => $totalReturnsValue,
            'net_sales' => $netSales,
            'cogs' => $actualCogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'net_profit' => $netProfit,
            'gross_margin' => $grossMargin,
            'net_margin' => $netMargin,
        ];
    }

    public function getTopProductsProperty()
    {
        // Items sold in period
        return SaleItem::whereHas('sale', function ($q) {
            $q->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])->where('status', '!=', 'cancelado');
        })
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(quantity * unit_price) as total_revenue'),
                DB::raw('SUM(quantity * COALESCE(unit_cost, 0)) as total_cost')
            )
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->map(function ($item) {
                $item->margin = $item->total_revenue - $item->total_cost;
                $item->margin_percent = $item->total_revenue > 0 ? ($item->margin / $item->total_revenue) * 100 : 0;
                return $item;
            })
            ->sortByDesc('margin')
            ->take(5);
    }

    public function getSalesBySellerProperty()
    {
        return Sale::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay()
        ])
            ->where('status', '!=', 'cancelado')
            ->with(['user', 'items'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($sales) {
                $user = $sales->first()->user;
                $totalSales = $sales->sum('total_amount');

                // Calculate simple COGS for this seller's sales
                $cogs = $sales->sum(function ($sale) {
                    return $sale->items->sum(fn($i) => $i->quantity * ($i->unit_cost ?? 0));
                });

                $grossProfit = $totalSales - $cogs;
                $margin = $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0;

                return [
                    'name' => $user->name,
                    'sales' => $totalSales,
                    'profit' => $grossProfit,
                    'margin' => $margin,
                    'count' => $sales->count()
                ];
            })
            ->sortByDesc('sales');
    }

    public function getTrendDataProperty()
    {
        // Daily trend for the selected period
        return Sale::whereBetween('created_at', [
            Carbon::parse($this->startDate)->startOfDay(),
            Carbon::parse($this->endDate)->endOfDay()
        ])
            ->where('status', '!=', 'cancelado')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function render()
    {
        return view('livewire.reports.sales-profitability-report', [
            'stats' => $this->stats,
            'topProducts' => $this->topProducts,
            'sellers' => $this->salesBySeller,
            'trend' => $this->trendData
        ])->layout('layouts.app');
    }
}
