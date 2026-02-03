<div class="p-6 bg-white shadow sm:rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Nueva Venta</h2>
    </div>

    {{-- Messages Handling --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm relative animate-in fade-out duration-1000 delay-[4000ms]">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 shadow-sm relative animate-in fade-out duration-1000 delay-[7000ms]">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        {{-- Left Column: Selection --}}
        <div>
            {{-- Client Selection --}}
            <div class="mb-6" wire:key="client-selector-container-{{ count($clients) }}"
                x-on:client-created.window="$wire.set('selected_client_id', $event.detail.clientId)">
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-medium text-gray-700">Seleccionar Cliente</label>
                    <button type="button" wire:click="openClientModal" 
                        class="text-[10px] font-black uppercase text-blue-600 hover:underline">
                        + Nuevo Cliente
                    </button>
                </div>
                <select wire:model.live="selected_client_id"
                    class="w-full border rounded p-2 bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Seleccione un cliente --</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                @error('selected_client_id') <span class="text-red-500 text-sm">Seleccione un cliente para
                continuar.</span> @enderror
            </div>

            {{-- Product Selection --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Agregar Producto</label>
                <select wire:model.live="selected_product_id"
                    class="w-full border rounded p-2 bg-white shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Seleccione un producto para agregar --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->name }} (₡{{ number_format($prod->sale_price, 0) }}) - Stock: {{ $prod->stock }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1 italic">* Al seleccionar se agregará automáticamente al carrito.
                </p>
            </div>
        </div>

        {{-- Right Column: Cart & Totals --}}
        <div class="bg-gray-50 p-4 rounded-lg border">
            <h3 class="font-bold text-lg mb-4">Carrito de Compra</h3>

            <div class="space-y-3 mb-6">
                @forelse($cart as $key => $item)
                    <div wire:key="cart-item-{{ $key }}" class="flex justify-between items-center bg-white p-3 rounded shadow-sm">
                        <div class="flex-1">
                            <div class="font-bold text-sm text-gray-800">{{ $item['name'] }}</div>
                            <div class="text-xs text-gray-500">Precio: ₡{{ number_format($item['price'], 0) }}</div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center border rounded-lg overflow-hidden bg-gray-50">
                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" 
                                    class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-600 font-bold"
                                    {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>-</button>
                                <span class="px-3 py-1 text-sm font-bold min-w-[2rem] text-center">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" 
                                    class="px-2 py-1 bg-white hover:bg-gray-100 text-gray-600 font-bold"
                                    {{ $item['quantity'] >= $item['max_stock'] ? 'disabled' : '' }}>+</button>
                            </div>
                            <div class="font-black text-sm min-w-[4rem] text-right text-blue-600 lowercase">
                                <span class="uppercase">₡</span>{{ number_format($item['price'] * $item['quantity'], 0) }}
                            </div>
                            <button wire:click="removeFromCart({{ $item['id'] }})"
                                class="text-red-400 hover:text-red-600 transition-colors p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-gray-400 text-center py-8 italic border-2 border-dashed border-gray-200 rounded-xl">
                        Aún no hay productos en el carrito.
                    </div>
                @endforelse
            </div>

            <div class="border-t pt-4 space-y-2">
                <div class="flex justify-between text-lg font-bold">
                    <span>Total:</span>
                    <span>₡{{ number_format($total_amount, 2) }}</span>
                </div>

                <div class="flex justify-between items-center bg-gray-100 p-2 rounded">
                    <span class="text-sm font-medium">Abono Inicial:</span>
                    <input wire:model.blur="initial_downpayment" type="number"
                        class="w-32 text-right border rounded p-1 bg-white">
                </div>

                <div class="flex justify-between items-center text-red-600 font-bold border-t pt-2 mt-2">
                    <span>Saldo Pendiente:</span>
                    <span>₡{{ number_format($current_balance, 2) }}</span>
                </div>
            </div>

            {{-- Payment Plan Section --}}
            @if($current_balance > 0)
                <div class="mt-4 border-t pt-4">
                    <h4 class="font-bold text-gray-700 mb-2">Plan de Pagos</h4>
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <div>
                            <label class="block text-xs text-gray-500">Plazos</label>
                            <input wire:model="number_of_installments" type="number" min="1"
                                class="w-full border rounded p-1">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 font-bold uppercase tracking-tight">Frecuencia (Desde Cliente)</label>
                            <select wire:model="quota_period" class="w-full border rounded p-1 bg-gray-50 text-gray-500 cursor-not-allowed" disabled>
                                <option value="diario">Diario</option>
                                <option value="semanal">Semanal</option>
                                <option value="quincenal">Quincenal</option>
                                <option value="mensual">Mensual</option>
                            </select>
                            <p class="text-[9px] text-blue-500 mt-1 italic">Definido en el perfil del cliente.</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" wire:click="calculateQuota" wire:loading.attr="disabled"
                            class="w-full bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 font-bold py-2 px-4 rounded-lg flex items-center justify-center transition mb-3 shadow-sm">
                            <svg wire:loading.remove xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <svg wire:loading class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span wire:loading.remove>Calcular Cuotas</span>
                            <span wire:loading>Calculando...</span>
                        </button>
                    </div>

                    <div class="flex justify-between items-center bg-blue-50 p-2 rounded border border-blue-200"
                        wire:key="quota-display-{{ $suggested_quota }}">
                        <span class="text-sm font-bold text-blue-800">Cuota Sugerida:</span>
                        <input wire:model.live="suggested_quota" type="number"
                            class="w-32 text-right font-bold text-blue-900 border-b border-blue-300 bg-transparent focus:outline-none">
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <button wire:click="confirmSave"
                    class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg shadow hover:bg-blue-700 disabled:opacity-50"
                    @if(empty($cart) || !$selected_client_id) disabled @endif>
                    Confirmar Venta
                </button>
            </div>
            @error('cart') <span class="text-red-500 text-sm block mt-2 text-center">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm p-4">
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-300">
                <div class="bg-blue-600 p-4 text-white">
                    <h3 class="text-lg font-black uppercase tracking-wider">Confirmar Nueva Venta</h3>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-gray-600 text-sm">¿Está seguro que desea registrar esta venta con los siguientes detalles?</p>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Cliente:</span>
                            <span class="font-bold text-gray-800">
                                {{ collect($clients)->firstWhere('id', $selected_client_id)['name'] ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Total Venta:</span>
                            <span class="font-bold text-gray-800">₡{{ number_format($total_amount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Abono Inicial:</span>
                            <span class="font-bold text-green-600">₡{{ number_format($initial_downpayment) }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-t pt-2 mt-2">
                            <span class="text-gray-500 font-bold">Saldo Pendiente:</span>
                            <span class="font-black text-red-600">₡{{ number_format($current_balance) }}</span>
                        </div>
                    </div>

                    @if($current_balance > 0)
                        <div class="text-[10px] text-gray-400 uppercase font-black text-center italic mt-2">
                            Plan: {{ $number_of_installments }} pagos {{ $quota_period }}s de
                            ₡{{ number_format($suggested_quota) }}
                        </div>
                    @endif

                    <div class="flex flex-col space-y-3 pt-4">
                        <button wire:click="save"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg transition-transform active:scale-95 flex items-center justify-center">
                            <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            SÍ, CONFIRMAR VENTA
                        </button>
                        <button wire:click="cancelConfirm"
                            class="w-full text-gray-400 text-sm font-bold hover:text-gray-600 transition underline decoration-dotted">
                            Cancelar y revisar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Success & WhatsApp Modal --}}
    @if($showSuccessModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900 bg-opacity-90 backdrop-blur-md p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in duration-300">
                <div class="bg-green-600 p-8 text-white text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-4">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black uppercase tracking-widest">¡Venta Exitosa!</h3>
                    <p class="text-green-100 text-sm mt-1">El registro se completó correctamente</p>
                </div>

                <div class="p-8 text-center space-y-6">
                    <div class="space-y-2">
                        <p class="text-gray-500 font-medium text-sm italic">¿Deseas enviar el comprobante al cliente por WhatsApp?</p>
                    </div>

                    <div class="flex flex-col space-y-3">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client_phone) }}?text={{ $whatsapp_message }}" 
                           target="_blank"
                           wire:click="closeSuccessModal"
                           class="w-full bg-[#25D366] hover:bg-[#128C7E] text-white font-black py-4 rounded-2xl shadow-lg shadow-green-100 transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center space-x-2">
                            <span class="text-xl">📱</span>
                            <span>ENVIAR COMPROBANTE</span>
                        </a>

                        <button wire:click="closeSuccessModal"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-500 font-black py-4 rounded-2xl transition-all font-black text-xs uppercase tracking-widest">
                            VOLVER AL INICIO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Client Creation Modal --}}
    @if($showClientModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="closeClientModal"></div>

                <div class="relative align-middle bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full">
                    <div class="bg-blue-600 p-6 text-white text-left">
                        <h3 class="text-xl font-black uppercase tracking-wider flex items-center">
                            <span class="mr-2">👤</span> Registro Rápido de Cliente
                        </h3>
                    </div>

                    <div class="p-8 text-left space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Nombre Completo</label>
                                <input type="text" wire:model="newClient.name" placeholder="Nombre completo del cliente"
                                    class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500">
                                @error('newClient.name') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Teléfono Principal</label>
                                <input type="text" wire:model="newClient.phone_primary" placeholder="Número celular"
                                    class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500">
                                @error('newClient.phone_primary') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Frecuencia</label>
                                <select wire:model.live="newClient.collection_frequency" 
                                    class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 font-bold text-gray-700">
                                    <option value="Diario">Diario</option>
                                    <option value="Semanal">Semanal</option>
                                    <option value="Quincenal">Quincenal</option>
                                    <option value="Mensual">Mensual</option>
                                </select>
                            </div>

                            @if(($newClient['collection_frequency'] ?? '') !== 'Diario')
                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">
                                        @if($newClient['collection_frequency'] === 'Semanal')
                                            Día de Cobro
                                        @elseif($newClient['collection_frequency'] === 'Quincenal')
                                            Periodo
                                        @elseif($newClient['collection_frequency'] === 'Mensual')
                                            Día del Mes
                                        @endif
                                    </label>
                                    
                                    @if($newClient['collection_frequency'] === 'Semanal')
                                        <select wire:model="newClient.collection_day" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 font-bold text-gray-700">
                                            @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
                                                <option value="{{ $day }}">{{ $day }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($newClient['collection_frequency'] === 'Quincenal')
                                        <select wire:model="newClient.collection_day" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 font-bold text-gray-700">
                                            <option value="15/30">Días 15 y 30</option>
                                            <option value="16/31">Días 16 y 31</option>
                                        </select>
                                    @elseif($newClient['collection_frequency'] === 'Mensual')
                                        <select wire:model="newClient.collection_day" class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 font-bold text-gray-700">
                                            @for($i = 1; $i <= 31; $i++)
                                                <option value="{{ $i }}">Día {{ $i }}</option>
                                            @endfor
                                        </select>
                                    @endif
                                    @error('newClient.collection_day') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Hora Sugerida</label>
                                <input type="time" wire:model="newClient.hora_cobro"
                                    class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500 font-bold text-gray-700">
                                @error('newClient.hora_cobro') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Dirección Exacta</label>
                            <textarea wire:model="newClient.address_details" rows="2" placeholder="Referencia de ubicación..."
                                class="w-full border-gray-200 rounded-xl shadow-sm focus:ring-blue-500"></textarea>
                            @error('newClient.address_details') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-6 grid grid-cols-2 gap-4">
                            <button type="button" wire:click="closeClientModal" 
                                class="w-full py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                                Cancelar
                            </button>
                            <button type="button" wire:click="saveNewClient" 
                                class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-blue-100 hover:bg-blue-700 transition">
                                Guardar Cliente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>