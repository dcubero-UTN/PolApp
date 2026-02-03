<div class="p-6 bg-white shadow sm:rounded-lg">
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $client->name }}</h2>
            <p class="text-sm text-gray-600">📞 {{ $client->phone_primary }}</p>
            <p class="text-sm text-gray-600">📍 {{ $client->address_details }}</p>
            
            <div class="mt-4 flex items-center bg-white border border-gray-200 rounded-xl px-4 py-2 w-fit shadow-sm">
                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest mr-4 border-r border-gray-100 pr-4">Responsable</span>
                <div class="flex items-center">
                    @role('admin')
                        <div class="relative flex items-center">
                            <select wire:model.live="selected_user_id" 
                                    class="text-sm font-bold text-blue-600 bg-transparent border-none p-0 pr-8 focus:ring-0 cursor-pointer appearance-none [background-image:none]">
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-0 pointer-events-none text-blue-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    @else
                        <span class="text-sm font-bold text-gray-700">{{ $client->user->name ?? 'Sin asignar' }}</span>
                    @endrole
                </div>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-600">Saldo Total</p>
            <p class="text-3xl font-bold {{ $client->current_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                ₡{{ number_format($client->current_balance, 2) }}
            </p>
        </div>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('clients.index') }}" class="text-blue-600 hover:underline font-bold text-sm">← Volver a lista</a>
        <a href="{{ route('clients.edit', $client) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:bg-indigo-700 transition flex items-center">
            <span class="mr-2">✏️</span> Editar Cliente
        </a>
    </div>

    {{-- Sales History --}}
    <div class="mt-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Historial de Ventas</h3>

        @forelse($client->sales->sortByDesc('created_at') as $sale)
            <div
                class="mb-4 p-4 border rounded-lg {{ $sale->status === 'pagado' ? 'bg-green-50 border-green-200' : ($sale->status === 'devuelto' ? 'bg-gray-50 border-gray-300' : 'bg-white border-gray-200') }}">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <a href="{{ route('sales.show', $sale) }}" class="group">
                            <h4 class="font-bold text-gray-800 group-hover:text-blue-600 transition flex items-center">
                                Venta #{{ $sale->id }}
                                <svg class="w-4 h-4 ml-1 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </h4>
                        </a>
                        <p class="text-xs text-gray-500">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <span
                            class="px-2 py-1 rounded text-xs font-bold
                                {{ $sale->status === 'pagado' ? 'bg-green-200 text-green-800' : ($sale->status === 'devuelto' ? 'bg-gray-300 text-gray-700' : 'bg-yellow-200 text-yellow-800') }}">
                            {{ strtoupper($sale->status) }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <p class="text-sm"><span class="font-medium">Total:</span> ₡{{ number_format($sale->total_amount, 2) }}
                    </p>
                    <p class="text-sm"><span class="font-medium">Abono Inicial:</span>
                        ₡{{ number_format($sale->initial_downpayment, 2) }}</p>
                    <p class="text-sm"><span class="font-medium">Saldo:</span>
                        <span class="{{ $sale->current_balance > 0 ? 'text-red-600' : 'text-green-600' }} font-bold">
                            ₡{{ number_format($sale->current_balance, 2) }}
                        </span>
                    </p>
                    @if($sale->suggested_quota > 0)
                        <p class="text-xs text-blue-600">Cuota sugerida: ₡{{ number_format($sale->suggested_quota, 2) }}
                            ({{ $sale->quota_period }})</p>
                    @endif
                </div>

                {{-- Products List --}}
                <div class="mb-3">
                    <p class="text-xs font-medium text-gray-700 mb-1">Productos:</p>
                    <ul class="text-xs text-gray-600 pl-4">
                        @foreach($sale->items as $item)
                            <li>{{ $item->product->name }} - {{ $item->quantity }} x ₡{{ number_format($item->unit_price, 2) }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Action Buttons --}}
                @if($sale->status === 'pendiente' && $sale->items->count() > 0)
                    <div class="flex space-x-2 mt-4">
                        <button wire:click="$dispatchTo('payment-modal', 'openPaymentModal', { clientId: {{ $client->id }}, saleId: {{ $sale->id }} })"
                            class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:bg-green-700 transition flex items-center">
                            <span class="mr-2">💰</span> Registrar Abono
                        </button>
                        <button wire:click="$dispatch('openReturnModal', { saleId: {{ $sale->id }} })"
                            class="bg-orange-500 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md hover:bg-orange-600 transition">
                            ↩️ Procesar Devolución
                        </button>
                    </div>
                @endif

                {{-- Returns History --}}
                @if($sale->returns && $sale->returns->count() > 0)
                    <div class="mt-3 pt-3 border-t border-gray-300">
                        <p class="text-xs font-medium text-gray-700 mb-1">Devoluciones:</p>
                        @foreach($sale->returns as $return)
                            <div class="text-xs text-gray-600 pl-4 mb-1">
                                <span class="font-medium">{{ $return->product->name }}</span>:
                                {{ $return->quantity }} unidad(es) -
                                <span class="{{ $return->product_condition === 'nuevo' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $return->product_condition === 'nuevo' ? '✅ Nuevo' : '❌ Dañado' }}
                                </span>
                                - ₡{{ number_format($return->refunded_amount, 2) }}
                                <span class="text-gray-500">({{ $return->created_at->format('d/m/Y') }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="text-gray-500 text-center py-8">No hay ventas registradas para este cliente.</p>
        @endforelse
    </div>

    <livewire:return-modal />
    <livewire:payment-modal wire:key="payment-modal-container" />
</div>