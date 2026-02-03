<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-extrabold text-gray-800 flex items-center">
            <span class="mr-2 text-blue-500">💰</span> Liquidaciones de Ruta
        </h2>
        <a href="{{ route('liquidations.create') }}"
            class="bg-blue-600 text-white px-6 py-2 rounded-xl text-sm font-black shadow-lg hover:bg-blue-700 transition uppercase">
            + Nuevo Cierre
        </a>
    </div>

    {{-- Messages Handling --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm relative animate-in fade-out duration-1000 delay-[4000ms]">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha /
                            Ruta</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Vendedor
                        </th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                            Recaudación</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                            Gastos</th>
                        <th
                            class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right text-blue-600">
                            Efectivo d/e</th>
                        <th
                            class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">
                            Estado</th>
                        <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($liquidations as $liq)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800">{{ $liq->date->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-gray-400 font-black uppercase tracking-tighter">ID:
                                    #{{ $liq->id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-600">{{ $liq->user->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span
                                    class="text-sm font-bold text-gray-800">₡{{ number_format($liq->total_recaudacion, 0) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span
                                    class="text-sm font-bold text-red-500">₡{{ number_format($liq->total_gastos, 0) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span
                                    class="text-sm font-black text-blue-600">₡{{ number_format($liq->total_a_entregar, 0) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($liq->status === 'confirmada')
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-600 text-[10px] font-black uppercase rounded-full">Liquidado</span>
                                @else
                                    <span
                                        class="px-3 py-1 bg-yellow-100 text-yellow-600 text-[10px] font-black uppercase rounded-full">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('liquidations.show', $liq->id) }}"
                                    class="text-blue-500 hover:text-blue-700 font-black text-xs uppercase tracking-widest">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400 italic">No hay liquidaciones registradas aún.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50">
            {{ $liquidations->links() }}
        </div>
    </div>
</div>