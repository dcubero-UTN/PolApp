<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-2xl rounded-3xl border border-gray-100">
        {{-- Header --}}
        <div
            class="bg-gradient-to-r {{ $isEdit ? 'from-indigo-600 to-indigo-500' : 'from-red-600 to-red-500' }} px-8 py-6">
            <h2 class="text-2xl font-black text-white uppercase tracking-wider flex items-center">
                <span class="mr-3">{{ $isEdit ? '✏️' : '💸' }}</span> {{ $isEdit ? 'Editar Gasto' : 'Registrar Gasto' }}
            </h2>
            <p class="text-red-100 text-sm mt-1 font-medium italic">
                {{ $isEdit ? 'Modifique los detalles del egreso solicitado.' : 'Complete los detalles del egreso y adjunte su comprobante.' }}
            </p>
        </div>

        <form wire:submit.prevent="save" class="p-8 space-y-8">
            {{-- Main Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Date --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Fecha del
                        Gasto</label>
                    <input type="date" wire:model="date"
                        class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-bold text-gray-700">
                    @error('date') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                {{-- Amount --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Importe Total
                        (₡)</label>
                    <input type="number" step="0.01" wire:model="amount" placeholder="0.00"
                        class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-black text-gray-800 text-xl">
                    @error('amount') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Place --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Lugar /
                        Establecimiento</label>
                    <input type="text" wire:model="place" placeholder="Ej: Gasolinera Santa Lucia"
                        class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-semibold">
                    @error('place') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                {{-- Provider --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Proveedor /
                        RUC</label>
                    <input type="text" wire:model="provider" placeholder="Nombre en la factura..."
                        class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-semibold">
                    @error('provider') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Category --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Categoría</label>
                    <select wire:model="category"
                        class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-bold text-gray-700">
                        <option value="">Seleccione categoría...</option>
                        <option value="Combustible">⛽ Combustible</option>
                        <option value="Comida">🍔 Comida / Alimentación</option>
                        <option value="Viáticos">🏨 Viáticos / Hospedaje</option>
                        <option value="Reparaciones">🔧 Reparaciones / Mantenimiento</option>
                        <option value="Otros">📦 Otros</option>
                    </select>
                    @error('category') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                {{-- Payment Method --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Forma de Pago</label>
                    <select wire:model="payment_method"
                        class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-bold text-gray-700">
                        <option value="">Seleccione pago...</option>
                        <option value="Efectivo">💵 Efectivo</option>
                        <option value="Tarjeta">💳 Tarjeta Crédito/Débito</option>
                        <option value="SINPE">📲 SINPE Móvil</option>
                    </select>
                    @error('payment_method') <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Concept --}}
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Concepto / Descripción
                    Corta</label>
                <input type="text" wire:model="concept" placeholder="Ej: Compra de Diesel para Ruta Norte"
                    class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-semibold">
                @error('concept') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
            </div>

            {{-- Justification --}}
            <div class="space-y-2">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Justificación del
                    Gasto</label>
                <textarea wire:model="justification" rows="3"
                    placeholder="Explique brevemente por qué fue necesario este gasto..."
                    class="w-full bg-gray-50 border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 py-3 px-4 font-medium"></textarea>
                @error('justification') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
            </div>

            {{-- Attachment --}}
            <div class="space-y-4">
                <label class="text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Comprobante (Foto del
                    Recibo)</label>
                <div class="flex items-center justify-center w-full">
                    <label
                        class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-200 border-dashed rounded-3xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition relative overflow-hidden">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="mb-2 text-sm text-gray-500 font-bold">Pulsa para tomar foto o subir archivo</p>
                            <p class="text-[10px] text-gray-400 font-black tracking-widest uppercase">JPG, PNG (MAX.
                                5MB)</p>
                        </div>
                        <input type="file" wire:model="attachment" class="hidden" accept="image/*"
                            capture="environment" />

                        @if ($attachment)
                            <div class="absolute inset-0 bg-white p-2">
                                <img src="{{ $attachment->temporaryUrl() }}"
                                    class="w-full h-full object-contain rounded-2xl shadow-inner">
                            </div>
                        @endif
                    </label>
                </div>
                @error('attachment') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                <div wire:loading wire:target="attachment" class="text-xs text-blue-500 font-black animate-pulse">
                    ⚡ SUBIENDO IMAGEN... UN MOMENTO
                </div>
            </div>

            {{-- Actions --}}
            <div class="pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-4">
                <a href="{{ route('expenses.index') }}"
                    class="px-8 py-4 text-center text-sm font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition">
                    Cancelar
                </a>
                <button type="submit" wire:loading.attr="disabled"
                    class="bg-red-600 text-white px-10 py-4 rounded-2xl text-base font-black shadow-xl hover:bg-red-700 transition transform hover:-translate-y-1 active:scale-95 flex items-center justify-center">
                    <span wire:loading.remove>📦 REGISTRAR GASTO</span>
                    <span wire:loading class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        PROCESANDO...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>