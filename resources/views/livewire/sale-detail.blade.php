<div class="p-6">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-black text-gray-800">Venta #{{ $sale->id }}</h2>
                <p class="text-gray-500">Registrada el {{ $sale->created_at->format('d/m/Y h:i A') }}</p>
            </div>
            <div class="text-right">
                <span class="px-4 py-2 rounded-full text-sm font-black uppercase tracking-widest
                    {{ $sale->status === 'pagado' ? 'bg-green-100 text-green-700' : ($sale->status === 'devuelto' ? 'bg-gray-100 text-gray-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ $sale->status }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Client Card --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Cliente</span>
                    <h3 class="text-xl font-bold text-gray-800">{{ $sale->client->name }}</h3>
                </div>
                <a href="{{ route('clients.show', $sale->client_id) }}" class="text-blue-600 text-sm font-bold hover:underline mt-4 inline-block">Ver Perfil del Cliente →</a>
            </div>

            {{-- Financial Summary --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Resumen Financiero</span>
                <div class="space-y-2 mt-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Venta:</span>
                        <span class="font-bold">₡{{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Abono Inicial:</span>
                        <span class="font-bold text-green-600">₡{{ number_format($sale->initial_downpayment, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-b pb-2">
                        <span class="text-gray-500">Total Abonos:</span>
                        <span class="font-bold text-blue-600">₡{{ number_format($sale->payments->sum('amount'), 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg pt-2">
                        <span class="font-bold text-gray-800">Saldo Actual:</span>
                        <span class="font-black text-red-600">₡{{ number_format($sale->current_balance, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Plan de Pagos --}}
            <div class="bg-blue-600 p-6 rounded-2xl shadow-lg text-white">
                <span class="text-[10px] font-bold text-blue-200 uppercase tracking-widest block mb-1">Plan de Pagos</span>
                <div class="mt-4">
                    <p class="text-2xl font-black">₡{{ number_format($sale->suggested_quota, 0) }}</p>
                    <p class="text-xs opacity-80 uppercase font-bold">{{ $sale->quota_period }} • {{ $sale->number_of_installments }} pagos</p>
                </div>
            </div>
        </div>

        {{-- Detail Tabs --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2 border-b">
                {{-- Column 1: Products --}}
                <div class="p-6 border-r">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <span class="mr-2 text-blue-600">📦</span> Productos en esta Venta
                    </h3>
                    <div class="space-y-3">
                        @foreach($sale->items as $item)
                            <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div>
                                    <p class="font-bold text-sm text-gray-800">{{ $item->product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->quantity }} unidad(es) x ₡{{ number_format($item->unit_price, 0) }}</p>
                                </div>
                                <span class="font-black text-gray-800">₡{{ number_format($item->quantity * $item->unit_price, 0) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Returns Section (If any) --}}
                    @if($sale->returns->count() > 0)
                        <div class="mt-8 pt-8 border-t border-dashed">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <span class="mr-2 text-orange-600">↩️</span> Devoluciones
                            </h3>
                            <div class="space-y-4">
                                @foreach($sale->returns as $return)
                                    <div class="bg-orange-50 p-4 rounded-2xl border border-orange-100">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="font-bold text-orange-800 text-sm">{{ $return->product->name }}</span>
                                            <span class="text-[10px] font-black uppercase text-orange-600 tracking-widest">{{ $return->product_condition }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-orange-700">Cant: {{ $return->quantity }} | Reingreso: {{ $return->restock ? 'SÍ' : 'NO' }}</span>
                                            <span class="font-black text-orange-900">-₡{{ number_format($return->refunded_amount, 0) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Column 2: History (Unified Timeline) --}}
                <div class="p-6 bg-gray-50/30">
                    <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                        <span class="mr-2 text-indigo-600">📜</span> Historial de Visitas y Pagos
                    </h3>
                    
                    @php
                        // Merge payments and collection attempts into a single timeline
                        $timeline = collect();
                        
                        foreach($sale->payments as $p) {
                            $timeline->push([
                                'type' => 'payment',
                                'date' => $p->created_at,
                                'amount' => $p->amount,
                                'method' => $p->payment_method,
                                'user' => $p->user->name,
                                'reference' => $p->reference_number
                            ]);
                        }
                        
                        foreach($sale->collectionAttempts->where('reason', '!=', 'pago_realizado') as $a) {
                            $timeline->push([
                                'type' => 'failure',
                                'date' => $a->created_at,
                                'reason' => $a->reason,
                                'notes' => $a->notes,
                                'user' => $a->user->name
                            ]);
                        }
                        
                        $sortedTimeline = $timeline->sortByDesc('date');
                    @endphp

                    @if($sortedTimeline->count() > 0)
                        <div class="space-y-6">
                            @foreach($sortedTimeline as $item)
                                <div class="relative pl-8 pb-2 border-l-2 {{ $item['type'] === 'payment' ? 'border-green-100' : 'border-orange-100' }} last:border-0">
                                    <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-2 border-white {{ $item['type'] === 'payment' ? 'bg-green-500' : 'bg-orange-500' }}"></div>
                                    
                                    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                {{ $item['date']->format('d M, Y - h:i A') }}
                                            </span>
                                            @if($item['type'] === 'payment')
                                                <span class="text-[10px] bg-green-100 text-green-700 px-2 py-1 rounded-full font-black uppercase">PAGO</span>
                                            @else
                                                <span class="text-[10px] bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-black uppercase">VISITA</span>
                                            @endif
                                        </div>

                                        @if($item['type'] === 'payment')
                                            <p class="text-xl font-black text-green-600">₡{{ number_format($item['amount'], 0) }}</p>
                                            <div class="flex items-center mt-1 space-x-2">
                                                <span class="text-xs font-bold text-gray-500 uppercase italic">{{ $item['method'] }}</span>
                                                @if($item['reference'])
                                                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded font-bold">Ref: {{ $item['reference'] }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <p class="font-bold text-orange-800 text-sm">
                                                {{ str_replace('_', ' ', ucfirst($item['reason'])) }}
                                            </p>
                                            @if($item['notes'])
                                                <p class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg mt-2 border border-gray-100 italic">"{{ $item['notes'] }}"</p>
                                            @endif
                                        @endif

                                        <p class="text-[10px] text-gray-400 mt-3">Gestión: <span class="font-bold">{{ $item['user'] }}</span></p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-white rounded-3xl border border-dashed">
                            <p class="text-gray-400 italic text-sm font-medium">No hay actividad registrada aún.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('clients.show', $sale->client_id) }}" class="text-gray-400 font-bold hover:text-gray-600 transition tracking-wide text-xs uppercase group flex items-center justify-center">
                <span class="mr-2 group-hover:-translate-x-1 transition-transform">←</span> Volver al Detalle del Cliente
            </a>
        </div>
    </div>
</div>
