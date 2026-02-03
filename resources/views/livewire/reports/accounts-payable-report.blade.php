<div class="p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase italic text-orange-600">Cuentas por
                Pagar</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mt-1">Gestión de Pasivos y
                Facturación Manual de Proveedores</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.accounts-payable.pdf', ['provider_id' => $providerId, 'start_date' => $startDate, 'end_date' => $endDate, 'only_pending' => $onlyPending ? 'true' : 'false']) }}" 
                target="_blank"
                class="bg-gray-800 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-black transition flex items-center">
                <span class="mr-2 text-lg">📄</span> EXPORTAR REPORTE
            </a>
            <a href="{{ route('reports.index') }}"
                class="bg-gray-100 text-gray-500 px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                VOLVER
            </a>
        </div>
    </div>

    {{-- Summary Card --}}
    <div
        class="bg-gradient-to-r from-orange-500 to-red-600 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-orange-100 mb-8 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-center">
            <div>
                <span
                    class="text-[10px] font-black uppercase tracking-[0.4em] text-orange-100 opacity-80 block mb-2">Total
                    Deuda Acumulada</span>
                <div class="text-6xl font-black tracking-tighter">
                    <span class="text-2xl align-top mr-1 font-bold">₡</span>{{ number_format($totalDebt, 0) }}
                </div>
            </div>
            <div class="mt-6 md:mt-0 text-center md:text-right">
                <div class="bg-white/20 backdrop-blur-md rounded-2xl px-6 py-4 border border-white/10">
                    <div class="text-[10px] font-black uppercase tracking-widest text-orange-100 mb-1">Proveedores
                        Activos con Deuda</div>
                    <div class="text-3xl font-black">
                        {{ $purchases->where('status', '!=', 'paid')->unique('provider_id')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white p-6 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Proveedor</label>
                <select wire:model.live="providerId"
                    class="w-full bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-200">
                    <option value="">Todos los proveedores</option>
                    @foreach($providers as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Desde</label>
                <input type="date" wire:model.live="startDate"
                    class="w-full bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-200">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Hasta</label>
                <input type="date" wire:model.live="endDate"
                    class="w-full bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-200">
            </div>
            <div class="flex items-center justify-end px-4">
                <label class="flex items-center cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" wire:model.live="onlyPending" class="sr-only">
                        <div
                            class="block bg-gray-200 w-14 h-8 rounded-full group-hover:bg-gray-300 transition-colors shadow-inner">
                        </div>
                        <div
                            class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition transform {{ $onlyPending ? 'translate-x-6 bg-orange-500 shadow-md' : 'translate-x-0' }}">
                        </div>
                    </div>
                    <div class="ml-3 text-[10px] font-black text-gray-500 uppercase tracking-widest">Solo Pendientes
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-gray-200 overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Factura /
                            Proveedor</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha /
                            Vencimiento</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                            Monto</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                            Saldo Pendiente</th>
                        <th
                            class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($purchases as $p)
                        @php
                            $isOverdue = $p->due_date && $p->due_date->isPast() && $p->status !== 'paid';
                            $expiresSoon = $p->due_date && $p->due_date->isFuture() && $p->due_date->diffInDays(now()) <= 7 && $p->status !== 'paid';
                            $rowBg = $p->status === 'paid' ? 'bg-gray-50/30 grayscale' : ($isOverdue ? 'bg-red-50/50' : ($expiresSoon ? 'bg-yellow-50/50' : ''));
                        @endphp
                        <tr class="{{ $rowBg }} transition-colors group">
                            <td class="px-8 py-6">
                                <div class="text-xs font-black text-gray-400 uppercase tracking-tighter mb-1">FACT
                                    #{{ $p->invoice_number ?: 'S/N' }}</div>
                                <div class="font-bold text-gray-800">{{ $p->provider->name }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-sm font-medium text-gray-600">{{ $p->purchase_date->format('d/m/Y') }}
                                </div>
                                @if($p->due_date)
                                    <div
                                        class="text-[10px] font-black uppercase mt-1 {{ $isOverdue ? 'text-red-500' : ($expiresSoon ? 'text-yellow-600' : 'text-gray-400') }}">
                                        {{ $isOverdue ? 'Vencida hace ' . $p->due_date->diffInDays(now()) . ' días' : ($expiresSoon ? 'Vence en ' . $p->due_date->diffInDays(now()) . ' días' : 'Vence el ' . $p->due_date->format('d/m/Y')) }}
                                    </div>
                                @else
                                    <div class="text-[10px] font-black text-gray-300 uppercase mt-1">Sin vencimiento</div>
                                @endif
                            </td>
                            <td class="px-8 py-6 text-right font-bold text-gray-400">
                                ₡{{ number_format($p->total_purchase, 0) }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div
                                    class="text-lg font-black {{ $p->status === 'paid' ? 'text-gray-400' : 'text-red-600' }}">
                                    ₡{{ number_format($p->balance, 0) }}</div>
                                <div
                                    class="text-[10px] font-black uppercase tracking-tighter {{ $p->status === 'paid' ? 'text-green-500' : 'text-gray-400' }}">
                                    {{ $p->status === 'paid' ? 'PAGADO' : 'PENDIENTE' }}
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                @if($p->status !== 'paid')
                                    <button wire:click="openPaymentModal({{ $p->id }})"
                                        class="bg-orange-500 text-white p-3 rounded-xl shadow-lg shadow-orange-100 hover:bg-orange-600 transition group-hover:scale-110">
                                        <span class="text-xs font-black uppercase tracking-widest px-2">ABONAR</span>
                                    </button>
                                @else
                                    <span
                                        class="text-xs font-black text-green-500 uppercase tracking-widest italic">Cerrado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="text-5xl mb-4 grayscale opacity-20">📂</div>
                                <div class="text-gray-400 italic font-medium">No se encontraron facturas pendientes.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-6 bg-gray-50/30">
            {{ $purchases->links() }}
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($showPaymentModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/90 backdrop-blur-md animate-in fade-in duration-300">
            <div
                class="bg-white rounded-[3rem] shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 animate-in zoom-in duration-300">
                <div class="bg-gradient-to-r from-orange-500 to-red-600 p-8 text-white relative">
                    <h3 class="text-2xl font-black uppercase tracking-tighter italic">Registrar Abono</h3>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-80">FACT
                        #{{ $selectedPurchase->invoice_number }} - {{ $selectedPurchase->provider->name }}</p>
                    <button wire:click="closePaymentModal"
                        class="absolute top-6 right-6 text-white/50 hover:text-white transition text-2xl">×</button>
                </div>

                <div class="p-8 space-y-6">
                    <div class="bg-orange-50 p-6 rounded-3xl border border-orange-100 flex justify-between items-center">
                        <span class="text-xs font-black text-orange-400 uppercase tracking-widest">Saldo Pendiente</span>
                        <span
                            class="text-2xl font-black text-orange-600 tracking-tighter">₡{{ number_format($selectedPurchase->balance, 0) }}</span>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Monto del
                                Abono</label>
                            <input type="number" wire:model.live="paymentAmount"
                                class="w-full bg-gray-50 border-none rounded-2xl text-xl font-black text-gray-800 focus:ring-4 focus:ring-orange-100">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Fecha de
                                    Pago</label>
                                <input type="date" wire:model="paymentDate"
                                    class="w-full bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Método</label>
                                <select wire:model="paymentMethod"
                                    class="w-full bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-700">
                                    <option value="transferencia">Transferencia</option>
                                    <option value="sinpe">SINPE Móvil</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">N° Referencia /
                                Comprobante</label>
                            <input type="text" wire:model="referenceNumber" placeholder="Opcional"
                                class="w-full bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-700 focus:ring-4 focus:ring-orange-100">
                        </div>
                    </div>

                    <button wire:click="recordPayment"
                        class="w-full bg-gray-800 text-white py-4 rounded-3xl font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:bg-black transition transform active:scale-95">
                        CONFIRMAR PAGO
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Floating Success Message --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="fixed bottom-10 right-10 z-[100] animate-in slide-in-from-right duration-500">
            <div
                class="bg-green-600 text-white px-8 py-4 rounded-2xl shadow-2xl font-black text-[10px] uppercase tracking-widest flex items-center">
                <span class="mr-2 text-lg">✅</span> {{ session('message') }}
            </div>
        </div>
    @endif
</div>