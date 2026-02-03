<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-extrabold text-gray-800 flex items-center">
                <span class="mr-2 text-orange-500">📥</span> Registrar Nueva Compra
            </h2>
            <a href="{{ route('purchases.index') }}" class="text-blue-600 hover:underline font-bold text-sm">
                ← Volver al historial
            </a>
        </div>

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r relative animate-in fade-out duration-1000 delay-[4000ms]"
                role="alert">
                <p class="font-bold">¡Éxito!</p>
                <p>{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded-r relative animate-in fade-out duration-1000 delay-[7000ms]"
                role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Form Column --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">Información de Cabecera
                    </h3>

                    <div class="space-y-4">
                        {{-- Provider Selection --}}
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase">Proveedor</label>
                                <button type="button" wire:click="openProviderModal"
                                    class="text-[10px] text-indigo-600 font-black uppercase hover:underline">
                                    + Nuevo Proveedor
                                </button>
                            </div>
                            <select wire:model="provider_id"
                                class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                                <option value="">Seleccione un proveedor...</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                                @endforeach
                            </select>
                            @error('provider_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Invoice Number --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Número de
                                Factura</label>
                            <input type="text" wire:model="invoice_number" placeholder="Referencia externa..."
                                class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                            @error('invoice_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Purchase Date --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Compra</label>
                            <input type="date" wire:model="purchase_date"
                                class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                            @error('purchase_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="bg-orange-600 p-6 rounded-2xl shadow-lg text-white">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Total de la Compra</p>
                    <p class="text-4xl font-black mt-1">₡{{ number_format($total_purchase, 2) }}</p>
                    <button wire:click="savePurchase" wire:loading.attr="disabled"
                        class="w-full mt-6 bg-white text-orange-600 font-black py-3 rounded-xl hover:bg-orange-50 transition shadow-md flex items-center justify-center">
                        <span wire:loading.remove>📦 CONFIRMAR INGRESO</span>
                        <span wire:loading>PROCESANDO...</span>
                    </button>
                </div>
            </div>

            {{-- Cart Column --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Product Search --}}
                <div class="relative">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Buscar Productos para
                        Ingresar</label>
                    <div class="flex items-center">
                        <input type="text" wire:model.live="search_product" placeholder="Buscar por nombre o SKU..."
                            class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-10 pr-4 py-3">
                        <div class="absolute left-3 top-[38px] text-gray-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    @if(!empty($searchResults))
                        <div
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden">
                            @foreach($searchResults as $product)
                                <button wire:click="addToCart({{ $product->id }})"
                                    class="w-full flex items-center justify-between p-4 hover:bg-indigo-50 border-b last:border-0 transition">
                                    <div class="text-left">
                                        <p class="font-bold text-gray-800">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $product->sku }} | Stock Actual:
                                            {{ $product->stock }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-indigo-600">
                                            ₡{{ number_format($product->cost_price, 2) }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Costo Actual</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Cart Items --}}
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-black text-gray-700 uppercase tracking-tighter text-sm">Detalle de Productos
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50 text-xs font-black text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-4">Producto</th>
                                    <th class="px-6 py-4 text-center">Cantidad</th>
                                    <th class="px-6 py-4 text-right">Costo Unitario</th>
                                    <th class="px-6 py-4 text-right">Subtotal</th>
                                    <th class="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($cart as $key => $item)
                                    <tr class="hover:bg-gray-50 transition" wire:key="cart-{{ $key }}">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-800 text-sm">{{ $item['name'] }}</p>
                                            <p class="text-[10px] text-gray-400 font-mono">{{ $item['sku'] }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <button type="button" wire:click="decrementQuantity('{{ $key }}')"
                                                    class="w-6 h-6 border rounded-full flex items-center justify-center hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition">
                                                    -
                                                </button>

                                                <input type="number"
                                                    wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                                                    value="{{ $item['quantity'] }}"
                                                    class="w-16 text-center border-gray-200 rounded-lg text-sm font-black focus:ring-orange-500 focus:border-orange-500 py-1">

                                                <button type="button" wire:click="incrementQuantity('{{ $key }}')"
                                                    class="w-6 h-6 border rounded-full flex items-center justify-center hover:bg-green-50 hover:border-green-200 hover:text-green-500 transition">
                                                    +
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end">
                                                <span class="mr-1 text-gray-400 text-xs">₡</span>
                                                <input type="number"
                                                    wire:change="updateCost('{{ $key }}', $event.target.value)"
                                                    value="{{ $item['unit_cost'] }}"
                                                    class="w-24 text-right border-gray-200 rounded-lg text-sm font-bold focus:ring-orange-500 focus:border-orange-500 py-1">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <p class="font-black text-gray-800">
                                                ₡{{ number_format($item['quantity'] * $item['unit_cost'], 2) }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button wire:click="removeFromCart('{{ $key }}')"
                                                class="text-red-300 hover:text-red-600 transition">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="text-4xl mb-2">🛒</span>
                                                <p class="text-gray-400 font-bold uppercase text-xs tracking-widest">El
                                                    carrito está vacío</p>
                                                <p class="text-gray-300 text-[10px] mt-1">Busque productos para iniciar el
                                                    ingreso de mercancía</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Provider Modal --}}
    @if($showProviderModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    wire:click="closeProviderModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="bg-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-black text-white uppercase tracking-wider flex items-center">
                            <span class="mr-2">🚛</span> Nuevo Proveedor
                        </h3>
                    </div>
                    <div class="px-6 py-4 space-y-4 bg-white">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre Comercial <span
                                    class="text-red-500">*</span></label>
                            <input type="text" wire:model="newProvider.name"
                                class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm">
                            @error('newProvider.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre de Contacto</label>
                            <input type="text" wire:model="newProvider.contact_name"
                                class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Teléfono</label>
                            <input type="text" wire:model="newProvider.phone"
                                class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dirección</label>
                            <textarea wire:model="newProvider.address" rows="2"
                                class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm"></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button type="button" wire:click="closeProviderModal"
                            class="px-4 py-2 text-sm font-bold text-gray-500 uppercase hover:text-gray-700 transition">
                            Cancelar
                        </button>
                        <button type="button" wire:click="saveProvider"
                            class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-black shadow-lg hover:bg-indigo-700 transition uppercase">
                            Guardar Proveedor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>