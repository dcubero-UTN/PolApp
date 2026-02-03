<div class="p-4 sm:p-6 max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 space-y-4 md:space-y-0">
        <div>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase">Liquidación de Ruta</h2>
            <div class="flex items-center mt-1 space-x-3 text-sm">
                <span
                    class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full font-black uppercase tracking-widest text-[10px]">
                    {{ \Carbon\Carbon::parse($date)->format('d F, Y') }}
                </span>
                <span class="text-gray-400 font-bold uppercase text-[10px]">Vendedor:
                    <span
                        class="text-gray-600">{{ $liquidation ? $liquidation->user->name : Auth::user()->name }}</span>
                </span>
            </div>
        </div>
        <div class="flex space-x-3 w-full md:w-auto">
            @if(!$is_view_only)
                <button wire:click="save"
                    class="flex-1 md:flex-none bg-blue-600 text-white px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-blue-100 hover:bg-blue-700 transition transform active:scale-95">
                    GENERAR CIERRE
                </button>
            @endif

            @if($is_view_only && $liquidation->status === 'pendiente' && auth()->user()->hasRole('admin'))
                <button wire:click="confirmLiquidation"
                    class="flex-1 md:flex-none bg-green-600 text-white px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-green-100 hover:bg-green-700 transition transform active:scale-95">
                    CONFIRMAR LIQUIDACIÓN
                </button>
            @endif

            @if($is_view_only)
                <a href="{{ route('liquidations.pdf', $liquidation->id) }}" 
                    target="_blank"
                    class="flex-1 md:flex-none bg-orange-500 text-white px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-orange-100 hover:bg-orange-600 transition transform active:scale-95 text-center">
                    EXPORTAR PDF
                </a>
            @endif

            <a href="{{ route('liquidations.index') }}"
                class="flex-1 md:flex-none bg-gray-100 text-gray-500 px-8 py-3 rounded-2xl font-black text-xs uppercase tracking-[0.2em] text-center hover:bg-gray-200 transition">
                VOLVER
            </a>
        </div>
    </div>

    {{-- KPIs Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Visitas del
                Día</span>
            <div class="text-3xl font-black text-gray-800">{{ $kpis['clientes_visitados'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Pagaron</span>
            <div class="text-3xl font-black text-green-600">{{ $kpis['clientes_pagaron'] }}</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Efectividad</span>
            <div class="text-3xl font-black text-blue-600">{{ number_format($kpis['efectividad'], 1) }}%</div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Ventas Nuevas</span>
            <div class="text-3xl font-black text-orange-600">₡{{ number_format($kpis['ventas_nuevas'] / 1000, 1) }}k
            </div>
        </div>
    </div>

    {{-- Main Financial Summary --}}
    <div class="bg-gray-900 rounded-[3rem] p-8 md:p-12 mb-8 text-white shadow-2xl relative overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-500 rounded-full opacity-10 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-500 rounded-full opacity-10 blur-3xl"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 relative z-10">
            {{-- Incomes & Expenses Split --}}
            <div class="space-y-6">
                <div>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] block mb-2">Ingresos
                        Totales</span>
                    <div class="text-4xl font-black">₡{{ number_format($summary['total_recaudacion'], 0) }}</div>
                    <div class="flex mt-2 space-x-4 text-xs opacity-60 font-bold uppercase tracking-tight">
                        <span>EFECTIVO: ₡{{ number_format($summary['total_efectivo'], 0) }}</span>
                        <span>DIGITAL: ₡{{ number_format($summary['total_transferencia'], 0) }}</span>
                    </div>
                </div>
                <div class="pt-6 border-t border-white/10">
                    <span class="text-[10px] font-black text-red-400 uppercase tracking-[0.3em] block mb-2">Gastos
                        Aprobados</span>
                    <div class="text-4xl font-black text-red-400">₡{{ number_format($summary['total_gastos'], 0) }}
                    </div>
                </div>
            </div>

            {{-- Result --}}
            <div class="bg-white/5 rounded-[2rem] p-8 border border-white/10 flex flex-col justify-center">
                <span
                    class="text-[10px] font-black text-blue-400 uppercase tracking-[0.4em] block mb-4 text-center">Efectivo
                    Físico a Entregar</span>
                <div class="text-6xl font-black text-center tracking-tighter">
                    <span
                        class="text-2xl text-blue-400 align-top mr-1">₡</span>{{ number_format($summary['total_a_entregar'], 0) }}
                </div>
                <p
                    class="text-[10px] text-center text-gray-400 mt-4 uppercase font-bold tracking-widest leading-relaxed">
                    Este monto corresponde únicamente al dinero en efectivo recaudado menos los gastos pagados en
                    efectivo.
                </p>
            </div>
        </div>
    </div>

    {{-- Details Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Payments List --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 italic">Detalle de Recaudación
                </h4>
                <span
                    class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-[10px] font-black">{{ count($payments) }}
                    pagos</span>
            </div>
            <div class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto">
                @forelse($payments as $pay)
                    <div class="p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <div>
                            <div class="font-bold text-gray-800 text-sm">{{ $pay->sale->client->name }}</div>
                            <div class="text-[10px] font-black text-gray-400 uppercase">{{ $pay->payment_method }} •
                                {{ $pay->created_at->format('H:i') }}</div>
                        </div>
                        <div class="text-sm font-black text-gray-800">₡{{ number_format($pay->amount, 0) }}</div>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-400 text-sm italic">No hay abonos registrados</div>
                @endforelse
            </div>
        </div>

        {{-- Expenses List --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h4 class="text-xs font-black uppercase tracking-widest text-red-400 italic">Detalle de Gastos</h4>
                <span
                    class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-[10px] font-black">{{ count($expenses) }}
                    autorizados</span>
            </div>
            <div class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto">
                @forelse($expenses as $exp)
                    <div class="p-4 flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            @if($exp->attachment_path)
                                <a href="{{ Storage::url($exp->attachment_path) }}" target="_blank" class="mr-3">
                                    <div
                                        class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center text-lg shadow-inner">
                                        📄</div>
                                </a>
                            @endif
                            <div>
                                <div class="font-bold text-gray-800 text-sm">{{ $exp->concept }}</div>
                                <div class="text-[10px] font-black text-gray-400 uppercase">{{ $exp->category }} •
                                    {{ $exp->payment_method }}</div>
                            </div>
                        </div>
                        <div class="text-sm font-black text-red-500">₡{{ number_format($exp->amount, 0) }}</div>
                    </div>
                @empty
                    <div class="p-12 text-center text-gray-400 text-sm italic">No hay gastos aprobados hoy</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Confirmation Modal (Admin) --}}
    @if(session()->has('message'))
        <div class="fixed bottom-10 right-10 z-50 animate-in slide-in-from-right duration-500">
            <div
                class="bg-green-600 text-white px-8 py-4 rounded-2xl shadow-2xl font-black text-xs uppercase tracking-widest">
                {{ session('message') }}
            </div>
        </div>
    @endif
</div>