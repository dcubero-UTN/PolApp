<div class="p-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase italic">Devoluciones y Mermas</h2>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mt-1">Auditoría de Retorno e
                Inventario Recuperado</p>
        </div>
        <div class="flex items-center space-x-3">
            <button wire:click="exportCsv"
                class="bg-green-600 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-green-100 hover:bg-green-700 transition flex items-center">
                <span class="mr-2 text-lg">📊</span> EXPORTAR EXCEL
            </button>
            <a href="{{ route('reports.index') }}"
                class="bg-gray-100 text-gray-500 px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                VOLVER
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white p-6 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
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
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Vendedor</label>
                <select wire:model.live="vendedorId"
                    class="w-full bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-200">
                    <option value="">Todos los vendedores</option>
                    @foreach($vendedores as $v)
                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2 ml-1">Estado</label>
                <select wire:model.live="condition"
                    class="w-full bg-gray-50 border-none rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-orange-200">
                    <option value="">Cualquier estado</option>
                    <option value="nuevo">Nuevo (Stock)</option>
                    <option value="dañado">Dañado (Merma)</option>
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="$refresh"
                    class="w-full bg-gray-800 text-white py-3 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition shadow-lg">
                    ACTUALIZAR
                </button>
            </div>
        </div>
    </div>

    {{-- KPI Highlights --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        <div
            class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 relative overflow-hidden">
            <div
                class="absolute -right-4 -bottom-4 w-24 h-24 bg-red-50 rounded-full opacity-50 flex items-center justify-center text-red-100 text-5xl">
                📝</div>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] block mb-2">Total Notas de
                Crédito</span>
            <div class="text-4xl font-black text-red-600 tracking-tighter">
                ₡{{ number_format($kpis['total_refunded'], 0) }}</div>
        </div>

        <div
            class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 relative overflow-hidden">
            <div
                class="absolute -right-4 -bottom-4 w-24 h-24 bg-green-50 rounded-full opacity-50 flex items-center justify-center text-green-100 text-5xl">
                📦</div>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] block mb-2">Unidades
                Recuperadas</span>
            <div class="text-4xl font-black text-green-600 tracking-tighter">
                {{ number_format($kpis['recovered_units'], 0) }} <span class="text-sm">unds</span></div>
        </div>

        <div
            class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 relative overflow-hidden">
            <div
                class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 flex items-center justify-center text-blue-100 text-5xl">
                📉</div>
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] block mb-2">Tasa de
                Devolución</span>
            <div class="text-4xl font-black text-blue-600 tracking-tighter">
                {{ number_format($kpis['return_rate'], 2) }}%</div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-gray-200 overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha /
                            Cliente</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Producto
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                            Valor Reintegrado</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Impacto Margen</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</th>
                        <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Motivo /
                            Vendedor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($returns as $ret)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="text-xs font-black text-gray-400 uppercase tracking-tighter mb-1">
                                    {{ $ret->created_at->format('d/m/Y H:i') }}</div>
                                <div class="font-bold text-gray-800">{{ $ret->sale->client->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold text-gray-700">{{ $ret->product->name ?? 'N/A' }}</div>
                                <a href="{{ route('sales.show', $ret->sale_id) }}" class="text-xs font-bold text-blue-500 hover:underline">
                                    ID Venta: #{{ $ret->sale_id }}
                                </a>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="text-md font-black text-red-500">₡{{ number_format($ret->refunded_amount, 0) }}
                                </div>
                                <div class="text-[10px] font-black text-gray-300 uppercase tracking-tighter">
                                    {{ $ret->quantity }} unidades</div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                @php
                                    $costValue = ($ret->product->cost_price ?? 0) * $ret->quantity;
                                    $marginLoss = $ret->product_condition === 'dañado' ? $costValue : ($ret->refunded_amount - $costValue);
                                @endphp
                                <div class="text-xs font-bold text-gray-600">₡{{ number_format($marginLoss, 0) }}</div>
                                <div class="text-[10px] uppercase font-black text-gray-300">Margen/Inversión</div>
                            </td>
                            <td class="px-8 py-6">
                                @if($ret->product_condition === 'nuevo')
                                    <span
                                        class="px-4 py-1.5 bg-green-100 text-green-600 text-[10px] font-black uppercase rounded-full shadow-sm">Nuevo
                                        / Stock</span>
                                @else
                                    <span
                                        class="px-4 py-1.5 bg-red-100 text-red-600 text-[10px] font-black uppercase rounded-full shadow-sm">Dañado
                                        / Merma</span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-xs text-gray-600 line-clamp-1 max-w-[200px]" title="{{ $ret->reason }}">
                                    {{ $ret->reason ?: 'Sin motivo especificado' }}</div>
                                <div class="text-[10px] font-black text-blue-500 uppercase tracking-widest mt-1">RESP:
                                    {{ $ret->user->name ?? 'N/A' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="text-5xl mb-4 grayscale opacity-20">📂</div>
                                <div class="text-gray-400 italic font-medium">No se encontraron devoluciones en el periodo
                                    seleccionado.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-6 bg-gray-50/30">
            {{ $returns->links() }}
        </div>
    </div>
</div>