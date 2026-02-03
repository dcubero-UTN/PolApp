<div>
    <div class="hidden">MODAL_LOADED</div>
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm p-4">
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden relative animate-in fade-in zoom-in duration-300">

                {{-- WhatsApp Receipt Overlay (Floating logic) --}}
                @if($whatsappLink)
                    <div class="absolute inset-0 z-10 bg-white flex flex-col items-center justify-center p-8 text-center">
                        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-800 mb-2">¡Gestión Finalizada!</h3>
                        <p class="text-gray-500 mb-8">La visita se ha registrado correctamente en el sistema.</p>

                        <div class="space-y-4 w-full">
                            <a href="{{ $whatsappLink }}" target="_blank"
                                class="flex items-center justify-center w-full bg-green-500 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-green-600 transition-all transform hover:scale-[1.02]">
                                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.038 3.234l-.692 2.154 2.211-.676c.945.512 1.966.822 3.209.823 3.181 0 5.767-2.584 5.768-5.766 0-3.181-2.584-5.767-5.766-5.767zm3.349 8.235c-.144.405-.833.725-1.146.767-.312.042-.626.071-1.751-.374-1.139-.452-1.928-1.463-2.495-2.22-.073-.1-.532-.71-.532-1.355 0-.644.337-.961.458-1.083.12-.12.261-.15.405-.15h.3c.099 0 .235-.042.349.204.144.307.495 1.201.538 1.292.043.09.072.197.014.316-.058.118-.087.193-.173.287-.087.094-.183.21-.261.285-.087.082-.178.172-.078.344.1.171.442.729.95 1.18.654.582 1.203.764 1.374.848.171.085.271.071.371-.042.1-.114.428-.5.542-.672.114-.171.228-.143.385-.085.157.058.995.469 1.166.555.171.085.285.128.328.201.043.073.043.418-.1-.01z" />
                                </svg>
                                Enviar Comprobante WhatsApp
                            </a>
                            <button wire:click="close"
                                class="block w-full text-gray-400 font-medium hover:text-gray-600 transition underline decoration-dotted">
                                Cerrar y seguir ruta
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Header --}}
                <div class="bg-blue-600 p-6 text-white flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-black">Gestionar Visita</h2>
                        <p class="text-blue-100 opacity-90 text-sm">Ruta: {{ $client?->name }}</p>
                    </div>
                    <button wire:click="close" class="text-white/80 hover:text-white text-3xl">&times;</button>
                </div>

                <div class="p-0 border-b flex bg-gray-50">
                    <button wire:click="$set('payment_mode', 'payment')"
                        class="flex-1 py-4 text-xs font-black uppercase tracking-widest transition-all {{ $payment_mode === 'payment' ? 'bg-white text-blue-600 border-b-2 border-blue-600' : 'text-gray-400 hover:text-gray-600' }}">
                        💰 Recibir Pago
                    </button>
                    <button wire:click="$set('payment_mode', 'failure')"
                        class="flex-1 py-4 text-xs font-black uppercase tracking-widest transition-all {{ $payment_mode === 'failure' ? 'bg-white text-orange-600 border-b-2 border-orange-600' : 'text-gray-400 hover:text-gray-600' }}">
                        ⚠️ Reportar Incumplimiento
                    </button>
                </div>

                <div class="p-6 space-y-8 overflow-y-auto max-h-[70vh]">

                    {{-- A. Información de Referencia --}}
                    <section class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Saldo
                                Actual</span>
                            <span
                                class="text-xl font-black text-red-600">₡{{ number_format($sale?->current_balance ?? 0, 0) }}</span>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Cuota
                                Pactada</span>
                            <span
                                class="text-xl font-black text-blue-600">₡{{ number_format($sale?->suggested_quota ?? 0, 0) }}</span>
                        </div>
                        <div class="col-span-2 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                            <span class="text-[10px] font-bold text-yellow-600 uppercase tracking-widest block mb-1">Última
                                Nota / Registro</span>
                            <p class="text-xs text-yellow-800 italic">"{{ $last_note }}"</p>
                        </div>
                    </section>

                    {{-- B. Acción Principal (Input de Abono) --}}
                    <section class="space-y-4">
                        @if($payment_mode === 'payment')
                            @if($sale)
                                <div class="text-center">
                                    <label class="block text-sm font-black text-gray-700 uppercase tracking-wider mb-2">Monto del Abono</label>
                                    <input wire:model.live="amount" type="number"
                                        class="w-full text-center text-4xl font-black text-green-600 bg-green-50/30 border-2 border-green-100 rounded-2xl p-4 focus:ring-green-500 focus:border-green-500 transition-all font-mono"
                                        placeholder="0">
                                    @error('amount') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
                                </div>

                                <div class="animate-in slide-in-from-top-4 duration-300 {{ $amount > 0 ? '' : 'opacity-30 pointer-events-none' }}">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Método de Pago</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button wire:click="$set('payment_method', 'efectivo')"
                                            class="py-3 px-4 rounded-xl border-2 font-bold transition flex items-center justify-center {{ $payment_method === 'efectivo' ? 'bg-blue-600 border-blue-600 text-white shadow-lg' : 'bg-white border-gray-100 text-gray-400 hover:border-blue-200' }}">
                                            💵 <span class="ml-2">Efectivo</span>
                                        </button>
                                        <button wire:click="$set('payment_method', 'sinpe')"
                                            class="py-3 px-4 rounded-xl border-2 font-bold transition flex items-center justify-center {{ $payment_method === 'sinpe' ? 'bg-blue-600 border-blue-600 text-white shadow-lg' : 'bg-white border-gray-100 text-gray-400 hover:border-blue-200' }}">
                                            📱 <span class="ml-2">SINPE</span>
                                        </button>
                                    </div>
                                    @if($payment_method === 'sinpe')
                                        <input wire:model="reference_number" type="text"
                                            placeholder="Referencia / Comprobante (Opcional)"
                                            class="w-full mt-3 border-gray-100 bg-gray-50 rounded-xl p-3 text-sm focus:ring-blue-500">
                                    @endif
                                </div>
                            @else
                                <div class="bg-gray-100 p-8 rounded-2xl text-center text-gray-500 border border-dashed border-gray-300">
                                    <p class="text-lg font-black italic">Sin deuda pendiente</p>
                                    <p class="text-[10px] uppercase tracking-widest mt-1">Este cliente está al día</p>
                                </div>
                            @endif
                        @else
                            {{-- C. Acción Alternativa (Gestión de No-Pago) --}}
                            <div class="bg-orange-50 p-6 rounded-3xl border border-orange-100 animate-in slide-in-from-bottom-4 duration-300">
                                <h4 class="text-sm font-black text-orange-800 uppercase mb-5 flex items-center">
                                    <svg class="w-6 h-6 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                    Reportar Incumplimiento
                                </h4>
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-[10px] font-black text-orange-400 uppercase tracking-widest mb-1 ml-1">Motivo Obligatorio</label>
                                        <select wire:model="attempt_reason"
                                            class="w-full border-none bg-white rounded-2xl p-4 shadow-sm focus:ring-orange-500 font-bold text-gray-700">
                                            <option value="">-- Seleccione el motivo... --</option>
                                            <option value="no_estaba">Cliente no estaba</option>
                                            <option value="sin_dinero">No tenía dinero</option>
                                            <option value="prorroga">Pidió prórroga</option>
                                            <option value="casa_cerrada">Casa cerrada / Se mudó</option>
                                            <option value="otro">Otro motivo</option>
                                        </select>
                                        @error('attempt_reason') <span class="text-red-500 text-[10px] mt-2 block font-black uppercase">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <textarea wire:model="attempt_notes" rows="3"
                                        class="w-full border-none bg-white rounded-2xl p-4 shadow-sm focus:ring-orange-500 placeholder-gray-300 text-sm font-medium"
                                        placeholder="Escriba aquí los detalles de la visita..."></textarea>

                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 ml-1">Próxima Visita (Opcional):</label>
                                        <input wire:model="next_visit_date" type="date"
                                            class="w-full border-none bg-white rounded-2xl p-4 shadow-sm text-sm font-bold text-blue-600">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </section>

                    <div class="flex flex-col space-y-3 pt-2">
                        @if($payment_mode === 'payment')
                            <button type="button" wire:click="finalizeVisit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-2xl shadow-xl transition-all flex items-center justify-center uppercase tracking-widest text-sm">
                                <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                ✅ Registrar Pago
                            </button>
                            <button type="button" wire:click="$set('payment_mode', 'failure')"
                                class="w-full bg-white text-orange-600 border-2 border-orange-100 font-black py-4 rounded-2xl hover:bg-orange-50 transition-all uppercase tracking-widest text-[10px]">
                                ⚠️ No se pudo cobrar / Cambiar a Incumplimiento
                            </button>
                        @else
                            <button type="button" wire:click="finalizeVisit"
                                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-black py-5 rounded-2xl shadow-xl transition-all flex items-center justify-center uppercase tracking-widest text-sm">
                                <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                💾 Guardar Incumplimiento
                            </button>
                            <button type="button" wire:click="$set('payment_mode', 'payment')"
                                class="w-full bg-white text-blue-600 border-2 border-blue-100 font-black py-4 rounded-2xl hover:bg-blue-50 transition-all uppercase tracking-widest text-[10px]">
                                💰 Volver a Recibir Pago
                            </button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:init', () => {
            console.log('PaymentModal initialized');
            Livewire.on('capture-gps', (event) => {
                console.log('Capturing GPS...');
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        console.log('GPS captured');
                        @this.set('attempt_latitude', position.coords.latitude, true);
                        @this.set('attempt_longitude', position.coords.longitude, true);
                    }, function (error) {
                        console.warn('GPS Error:', error.message);
                    }, { timeout: 6000, enableHighAccuracy: false });
                }
            });
        });
    </script>
</div>