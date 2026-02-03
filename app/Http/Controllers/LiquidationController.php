<?php

namespace App\Http\Controllers;

use App\Models\Liquidation;
use Barryvdh\Dompdf\Facade\Pdf;
use Illuminate\Http\Request;

class LiquidationController extends Controller
{
    public function downloadPDF(Liquidation $liquidation)
    {
        $liquidation->load(['user', 'payments.sale.client', 'expenses']);

        $pdf = Pdf::loadView('pdfs.liquidation', [
            'liquidation' => $liquidation,
            'user' => $liquidation->user,
            'payments' => $liquidation->payments,
            'expenses' => $liquidation->expenses,
        ]);

        return $pdf->download("Liquidacion-{$liquidation->date->format('d-m-Y')}-{$liquidation->user->name}.pdf");
    }
}
