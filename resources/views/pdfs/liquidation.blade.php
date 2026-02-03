<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Liquidación - PolaApp</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #2563eb; text-transform: uppercase; font-size: 20px; }
        .header p { margin: 5px 0; color: #666; font-weight: bold; }
        
        .section-header { background: #f3f4f6; padding: 10px; font-weight: bold; text-transform: uppercase; font-size: 10px; margin-top: 20px; border-left: 4px solid #2563eb; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .summary-table td { padding: 8px; border-bottom: 1px solid #eee; }
        .summary-table .label { color: #666; font-weight: bold; width: 40%; }
        .summary-table .value { text-align: right; font-weight: bold; }
        .summary-table .total-row { background: #2563eb; color: white; font-size: 14px; }
        .summary-table .total-row td { padding: 15px 10px; }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        .items-table th { background: #f9fafb; padding: 8px; text-align: left; border-bottom: 1px solid #ddd; color: #666; text-transform: uppercase; }
        .items-table td { padding: 8px; border-bottom: 1px solid #eee; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        
        .kpi-container { margin-top: 20px; }
        .kpi-box { display: inline-block; width: 23%; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; }
        .kpi-box span { display: block; font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .kpi-box strong { font-size: 14px; color: #1e293b; }

        .text-red { color: #ef4444; }
        .text-green { color: #22c55e; }
        .text-blue { color: #2563eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PolaApp - Liquidación de Ruta</h1>
        <p>Vendedor: {{ $user->name }} | Fecha: {{ $liquidation->date->format('d/m/Y') }}</p>
    </div>

    <div class="kpi-container">
        <div class="kpi-box">
            <span>Visitas</span>
            <strong>{{ $liquidation->clientes_visitados }}</strong>
        </div>
        <div class="kpi-box">
            <span>Pagaron</span>
            <strong>{{ $liquidation->clientes_pagaron }}</strong>
        </div>
        <div class="kpi-box">
            <span>Efectividad</span>
            <strong>{{ number_format($liquidation->efectividad, 1) }}%</strong>
        </div>
        <div class="kpi-box">
            <span>Ventas Nuevas</span>
            <strong>₡{{ number_format($liquidation->ventas_nuevas, 0) }}</strong>
        </div>
    </div>

    <div class="section-header">Resumen Financiero</div>
    <table class="summary-table">
        <tr>
            <td class="label">Total Recaudación:</td>
            <td class="value">₡{{ number_format($liquidation->total_recaudacion, 2) }}</td>
        </tr>
        <tr>
            <td class="label"> - Recaudado en Efectivo:</td>
            <td class="value">₡{{ number_format($liquidation->total_efectivo, 2) }}</td>
        </tr>
        <tr>
            <td class="label"> - Recaudado por Transferencia/SINPE:</td>
            <td class="value">₡{{ number_format($liquidation->total_transferencia, 2) }}</td>
        </tr>
        <tr>
            <td class="label text-red">Total Gastos Aprobados:</td>
            <td class="value text-red"> - ₡{{ number_format($liquidation->total_gastos, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td class="label" style="color: white;">EFECTIVO A ENTREGAR:</td>
            <td class="value">₡{{ number_format($liquidation->total_a_entregar, 2) }}</td>
        </tr>
    </table>

    <div class="section-header">Detalle de Ingresos (Pagos)</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Método</th>
                <th style="text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $pay)
                <tr>
                    <td>{{ $pay->sale->client->name }}</td>
                    <td>{{ ucfirst($pay->payment_method) }}</td>
                    <td style="text-align: right; font-weight: bold;">₡{{ number_format($pay->amount, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($expenses->count() > 0)
        <div class="section-header">Detalle de Egresos (Gastos)</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Categoría</th>
                    <th>Método</th>
                    <th style="text-align: right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $exp)
                    <tr>
                        <td>{{ $exp->concept }}</td>
                        <td>{{ $exp->category }}</td>
                        <td>{{ ucfirst($exp->payment_method) }}</td>
                        <td style="text-align: right; font-weight: bold; color: #ef4444;">₡{{ number_format($exp->amount, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }} vía PolaApp</p>
        <p>Este documento es un comprobante oficial de liquidación de ruta.</p>
    </div>
</body>
</html>
