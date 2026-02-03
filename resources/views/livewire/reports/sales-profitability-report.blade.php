<div class="p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase italic text-blue-900">Rentabilidad & Ventas</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mt-1">Análisis Financiero y Desempeño Comercial</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.index') }}" 
                class="bg-gray-100 text-gray-500 px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                VOLVER
            </a>
        </div>
    </div>

    {{-- Controls --}}
    <div class="bg-white p-6 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Desde</label>
                <input type="date" wire:model.live="startDate" 
                    class="w-full bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Hasta</label>
                <input type="date" wire:model.live="endDate" 
                    class="w-full bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-blue-200">
            </div>
            <div class="flex items-end">
                <div class="w-full bg-blue-50 rounded-xl p-3 flex items-center justify-between">
                    <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Periodo Seleccionado</span>
                    <span class="font-bold text-blue-800 text-sm">
                        {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Financial Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Net Sales --}}
        <div class="bg-white p-6 rounded-[2.5rem] shadow-lg shadow-gray-100 border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Ventas Netas</span>
                <div class="text-3xl font-black text-blue-900 tracking-tighter mb-1">₡{{ number_format($stats['net_sales'] / 1000, 1) }}k</div>
                <div class="text-[10px] font-bold text-gray-400">Bruto: ₡{{ number_format($stats['gross_sales'] / 1000, 1) }}k</div>
            </div>
        </div>

        {{-- COGS --}}
        <div class="bg-white p-6 rounded-[2.5rem] shadow-lg shadow-gray-100 border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-red-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Costo de Ventas</span>
                <div class="text-3xl font-black text-red-800 tracking-tighter mb-1">₡{{ number_format($stats['cogs'] / 1000, 1) }}k</div>
                <div class="text-[10px] font-bold text-red-300">{{ number_format(($stats['cogs'] / ($stats['net_sales'] ?: 1)) * 100, 1) }}% de ventas</div>
            </div>
        </div>

        {{-- Expenses --}}
        <div class="bg-white p-6 rounded-[2.5rem] shadow-lg shadow-gray-100 border border-gray-100 relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-orange-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Gastos Operativos</span>
                <div class="text-3xl font-black text-orange-600 tracking-tighter mb-1">₡{{ number_format($stats['expenses'] / 1000, 1) }}k</div>
                <div class="text-[10px] font-bold text-orange-300">Aprobados</div>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-[2.5rem] shadow-xl shadow-green-100 text-white relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500 blur-xl"></div>
            <div class="relative z-10">
                <span class="text-[10px] font-black text-green-100 uppercase tracking-widest block mb-2">Utilidad Neta</span>
                <div class="text-4xl font-black tracking-tighter mb-1">₡{{ number_format($stats['net_profit'], 0) }}</div>
                <div class="inline-block bg-white/20 px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wide">
                    Margen: {{ number_format($stats['net_margin'], 1) }}%
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        {{-- Top Products Table --}}
        <div class="lg:col-span-2 bg-white rounded-[2.5rem] shadow-lg shadow-gray-100 border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-black uppercase tracking-widest text-gray-500 italic">Top Productos (Rentabilidad)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase">Producto</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Cant.</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Venta Total</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Utilidad</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase text-right">Margen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($topProducts as $prod)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 text-xs">{{ $prod->product->name }}</div>
                                    <div class="text-[9px] text-gray-400 uppercase tracking-wider">{{ $prod->product->sku }}</div>
                                </td>
                                <td class="px-6 py-4 text-right text-xs font-bold text-gray-600">{{ $prod->total_qty }}</td>
                                <td class="px-6 py-4 text-right text-xs font-bold text-gray-600">₡{{ number_format($prod->total_revenue, 0) }}</td>
                                <td class="px-6 py-4 text-right text-xs font-bold text-green-600">₡{{ number_format($prod->margin, 0) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg text-[10px] font-black">
                                        {{ number_format($prod->margin_percent, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sellers Performance --}}
        <div class="bg-white rounded-[2.5rem] shadow-lg shadow-gray-100 border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50">
                <h3 class="text-sm font-black uppercase tracking-widest text-gray-500 italic">Ranking Vendedores</h3>
            </div>
            <div class="p-4 space-y-4">
                @foreach($sellers as $seller)
                    <div class="bg-gray-50 rounded-2xl p-4 relative overflow-hidden group hover:bg-white hover:shadow-md transition-all border border-transparent hover:border-gray-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-800 text-sm">{{ $seller['name'] }}</span>
                            <span class="text-[10px] font-black text-blue-500 bg-blue-50 px-2 py-0.5 rounded-full">{{ $seller['count'] }} ventas</span>
                        </div>
                        <div class="flex justify-between items-end">
                            <div>
                                <div class="text-[10px] text-gray-400 uppercase">Ventas</div>
                                <div class="font-black text-gray-700">₡{{ number_format($seller['sales'] / 1000, 1) }}k</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] text-gray-400 uppercase">Utilidad</div>
                                <div class="font-black text-green-600">₡{{ number_format($seller['profit'] / 1000, 1) }}k</div>
                            </div>
                        </div>
                        {{-- Mini Margin Bar --}}
                        <div class="mt-3 w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-green-500 h-full rounded-full" style="width: {{ min($seller['margin'], 100) }}%"></div>
                        </div>
                        <div class="text-[9px] text-right mt-1 text-gray-400 font-bold">{{ number_format($seller['margin'], 1) }}% margen</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Trend Chart (Simulated with bars for now, could be replaced with Chart.js) --}}
    <div class="bg-white rounded-[2.5rem] shadow-lg shadow-gray-100 border border-gray-100 p-8">
        <h3 class="text-sm font-black uppercase tracking-widest text-gray-500 italic mb-6">Tendencia Diaria (Ventas)</h3>
        <div class="h-48 flex items-end space-x-2">
            @php $maxTrend = $trend->max('total') ?: 1; @endphp
            @foreach($trend as $day)
                <div class="flex-1 flex flex-col justify-end group cursor-pointer relative">
                    <div class="w-full bg-blue-100 rounded-t-lg hover:bg-blue-400 transition-all relative" 
                        style="height: {{ ($day->total / $maxTrend) * 100 }}%">
                        
                        {{-- Tooltip --}}
                        <div class="opacity-0 group-hover:opacity-100 absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-gray-800 text-white text-[10px] font-bold py-1 px-2 rounded-lg whitespace-nowrap z-20 pointer-events-none transition-opacity">
                            ₡{{ number_format($day->total, 0) }}
                            <div class="text-[8px] opacity-70">{{ \Carbon\Carbon::parse($day->date)->format('d M') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between mt-2 text-[9px] font-bold text-gray-400 uppercase tracking-wider">
            <span>{{ \Carbon\Carbon::parse($startDate)->format('d M') }}</span>
            <span>{{ \Carbon\Carbon::parse($endDate)->format('d M') }}</span>
        </div>
    </div>
</div>
