<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Client;
use App\Models\DailyVisit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardAdmin extends Component
{
    public function render()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $today = Carbon::today();
        $todayDate = $today->toDateString();

        // 1. Weekly Sales Total
        $weeklySales = Sale::where('created_at', '>=', $startOfWeek)->sum('total_amount');

        // 2. Collections Today (Abonos)
        $collectionsToday = Payment::whereDate('created_at', $today)->sum('amount');

        // 3. Today's Route Coverage
        $daysMap = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'];
        $todayDay = $daysMap[date('w')] ?? 'Lunes';

        $totalRuta = Client::where(function ($q) use ($todayDay, $todayDate) {
            $q->where('collection_day', $todayDay)
                ->orWhere('next_visit_date', $todayDate);
        })->count();

        $visitedCount = DailyVisit::where('visit_date', $todayDate)
            ->where('completed', true)
            ->count();

        // 4. Low Stock Products
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock_alert')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return view('livewire.dashboard-admin', [
            'weeklySales' => $weeklySales,
            'collectionsToday' => $collectionsToday,
            'routeCoverage' => $totalRuta > 0 ? round(($visitedCount / $totalRuta) * 100) : 0,
            'totalRuta' => $totalRuta,
            'visitedCount' => $visitedCount,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
