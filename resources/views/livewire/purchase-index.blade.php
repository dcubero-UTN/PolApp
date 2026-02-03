<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-extrabold text-gray-800 flex items-center">
                <span class="mr-2 text-orange-500">📜</span> Historial de Compras
            </h2>
            <a href="{{ route('purchases.create') }}"
                class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-black shadow-lg hover:bg-indigo-700 transition uppercase">
                + Nueva Compra
            </a>
        </div>

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r relative animate-in fade-out duration-1000 delay-[4000ms]"
                role="alert">
                <p>{{ session('message') }}</p>
            </div>
        @endif

        <div class="mb-6">
            <input type="text" wire:model.live="search" placeholder="Buscar por factura o proveedor..."
                class="w-full md:w-1/3 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="overflow-x-auto bg-white border border-gray-100 rounded-2xl shadow-sm">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs font-black text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-center">ID</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Proveedor</th>
                        <th class="px-6 py-4">Factura</th>
                        <th class="px-6 py-4 text-center">Items</th>
                        <th class="px-6 py-4 text-right">Total</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="bg-gray-100 text-gray-600 text-[10px] font-black px-2 py-1 rounded">#{{ $purchase->id }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                {{ $purchase->purchase_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800">
                                {{ $purchase->provider->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                {{ $purchase->invoice_number ?? '---' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-2 py-1 rounded-full uppercase">
                                    {{ $purchase->items->count() }} prod.
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-900">
                                ₡{{ number_format($purchase->total_purchase, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                {{-- Could add a view detail button here --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 font-medium">
                                No se encontraron compras registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $purchases->links() }}
        </div>
    </div>
</div>