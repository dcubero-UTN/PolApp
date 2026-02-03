<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Provider;
use Barryvdh\Dompdf\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function accountsPayablePDF(Request $request)
    {
        $providerId = $request->get('provider_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $onlyPending = $request->get('only_pending', true);

        $query = Purchase::with(['provider', 'payments'])
            ->when($startDate, fn($q) => $q->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('purchase_date', '<=', $endDate))
            ->when($providerId, fn($q) => $q->where('provider_id', $providerId))
            ->when($onlyPending == 'true' || $onlyPending === true, fn($q) => $q->where('status', '!=', 'paid'));

        $purchases = $query->orderBy('purchase_date', 'asc')->get();
        $totalDebt = $purchases->sum(fn($p) => $p->balance);

        $provider = $providerId ? Provider::find($providerId) : null;

        $pdf = Pdf::loadView('pdfs.accounts-payable', [
            'purchases' => $purchases,
            'totalDebt' => $totalDebt,
            'provider' => $provider,
            'dateRange' => [
                'start' => $startDate ? Carbon::parse($startDate)->format('d/m/Y') : null,
                'end' => $endDate ? Carbon::parse($endDate)->format('d/m/Y') : null,
            ]
        ]);

        $filename = "Estado_Cuenta_" . ($provider ? $provider->name : "General") . "_" . now()->format('d-m-Y') . ".pdf";
        return $pdf->download($filename);
    }
}
