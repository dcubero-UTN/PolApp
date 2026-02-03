<div>
    @if($showModal && $sale)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-md p-6 relative">
                <button wire:click="close"
                    class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>

                <h3 class="text-lg font-bold text-gray-800 mb-4">Procesar Devolución</h3>

                @if(session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-4">
                    <p class="text-sm text-gray-600">Cliente: <span class="font-bold">{{ $sale->client->name }}</span></p>
                    <p class="text-sm text-gray-600">Venta ID: <span class="font-bold">#{{ $sale->id }}</span></p>
                    <p class="text-sm text-gray-600">Saldo Actual: <span
                            class="font-bold text-red-600">₡{{ number_format($sale->current_balance, 2) }}</span></p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Producto a Devolver *</label>
                    <select wire:model.live="selectedProduct" class="w-full border rounded p-2">
                        <option value="">Seleccione un producto...</option>
                        @foreach($sale->items as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->product->name }} ({{ $item->quantity }} unidades -
                                ₡{{ number_format($item->unit_price, 2) }} c/u)
                            </option>
                        @endforeach
                    </select>
                    @error('selectedProduct') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                @if($selectedProduct)
                    @php
                        $saleItem = $sale->items->firstWhere('id', $selectedProduct);
                    @endphp

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                        <input wire:model="quantity" type="number" min="1" max="{{ $saleItem->quantity }}"
                            class="w-full border rounded p-2">
                        <p class="text-xs text-gray-500 mt-1">Máximo: {{ $saleItem->quantity }}</p>
                        @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Condición del Producto *</label>
                        <div class="flex space-x-2">
                            <button wire:click="$set('product_condition', 'nuevo')"
                                class="flex-1 py-2 px-4 rounded-lg font-bold transition {{ $product_condition === 'nuevo' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                ✅ Nuevo
                            </button>
                            <button wire:click="$set('product_condition', 'dañado')"
                                class="flex-1 py-2 px-4 rounded-lg font-bold transition {{ $product_condition === 'dañado' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                ❌ Dañado
                            </button>
                        </div>
                        @if($product_condition === 'dañado')
                            <p class="text-xs text-red-600 mt-1">⚠️ No se reintegrará al inventario disponible</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de Devolución</label>
                        <textarea wire:model="reason" rows="2" class="w-full border rounded p-2"
                            placeholder="Razón de la devolución..."></textarea>
                    </div>

                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm font-bold text-blue-800">Resumen</p>
                        <p class="text-xs text-gray-700">Monto a reembolsar: <span
                                class="font-bold">₡{{ number_format($saleItem->unit_price * $quantity, 2) }}</span></p>
                        <p class="text-xs text-gray-700">Nuevo saldo: <span
                                class="font-bold">₡{{ number_format($sale->current_balance - ($saleItem->unit_price * $quantity), 2) }}</span>
                        </p>
                    </div>

                    <button wire:click="confirmReturn"
                        class="w-full bg-blue-600 text-white font-black py-4 rounded-xl shadow-lg hover:bg-blue-700 transition uppercase tracking-wider text-sm mt-4">
                        Confirmar Devolución
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm p-4 text-center">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden p-8">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-50 mb-6">
                    <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter mb-2">¿Procesar Devolución?</h3>
                <p class="text-gray-500 text-xs font-medium mb-8">Esta acción reintegrará el monto al saldo del cliente y
                    registrará el movimiento en el historial.</p>

                <div class="flex flex-col space-y-3">
                    <button wire:click="processReturn"
                        class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-100 hover:bg-blue-700 transition">
                        Sí, Registrar Devolución
                    </button>
                    <button wire:click="cancelConfirm"
                        class="w-full py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                        No, Revisar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>