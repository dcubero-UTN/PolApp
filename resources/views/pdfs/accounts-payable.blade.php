<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Estado de Cuenta Proveedor - PolaApp</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ea580c;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            color: #ea580c;
            text-transform: uppercase;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-weight: bold;
        }

        .summary-box {
            background: #fff7ed;
            border: 1px solid #ffedd5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .summary-box table {
            width: 100%;
        }

        .summary-box .total {
            font-size: 20px;
            color: #c2410c;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items-table th {
            background: #f8fafc;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            color: #64748b;
            text-transform: uppercase;
            font-size: 9px;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        .overdue {
            color: #ef4444;
            font-weight: bold;
        }

        .paid {
            color: #22c55e;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-pending {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-credit {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-paid {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>PolaApp - Estado de Cuenta</h1>
        <p>
            @if($provider)
                Proveedor: {{ $provider->name }}
            @else
                Consolidado de Cuentas por Pagar
            @endif
        </p>
        <p>Fecha de Generación: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <td>
                    <span style="text-transform: uppercase; font-weight: bold; color: #9a3412;">Saldo Total
                        Pendiente:</span><br>
                    <span class="total">₡{{ number_format($totalDebt, 0) }}</span>
                </td>
                <td style="text-align: right;">
                    @if($dateRange['start'] || $dateRange['end'])
                        <span style="color: #64748b;">Periodo: {{ $dateRange['start'] ?? '...' }} al
                            {{ $dateRange['end'] ?? '...' }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Factura</th>
                @if(!$provider)
                <th>Proveedor</th> @endif
                <th>Emisión</th>
                <th>Vencimiento</th>
                <th style="text-align: right;">Monto Total</th>
                <th style="text-align: right;">Saldo</th>
                <th style="text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $p)
                <tr>
                    <td>#{{ $p->invoice_number ?: 'S/N' }}</td>
                    @if(!$provider)
                    <td>{{ $p->provider->name }}</td> @endif
                    <td>{{ $p->purchase_date->format('d/m/Y') }}</td>
                    <td class="{{ ($p->due_date && $p->due_date->isPast() && $p->status != 'paid') ? 'overdue' : '' }}">
                        {{ $p->due_date ? $p->due_date->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td style="text-align: right;">₡{{ number_format($p->total_purchase, 0) }}</td>
                    <td style="text-align: right; font-weight: bold;">₡{{ number_format($p->balance, 0) }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-{{ $p->status }}">
                            {{ $p->status == 'paid' ? 'Pagado' : ($p->status == 'credit' ? 'Crédito' : 'Pendiente') }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento es un resumen informativo de las obligaciones pendientes registradas en PolaApp.</p>
        <p>Cualquier discrepancia debe ser conciliada con el departamento de contabilidad.</p>
    </div>
</body>

</html>