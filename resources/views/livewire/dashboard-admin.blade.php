<div class="mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <span class="mr-2 text-blue-600">📊</span> Métricas Administrativas
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {{-- Weekly Sales --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="text-sm font-medium text-gray-500 mb-1">Ventas de la Semana</div>
            <div class="text-2xl font-black text-gray-900">₡{{ number_format($weeklySales, 0) }}</div>
            <div class="mt-2 flex items-center text-xs text-green-600">
                <span class="font-bold">Total Facturado</span>
            </div>
        </div>

        {{-- Collections Today --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="text-sm font-medium text-gray-500 mb-1">Cobrado Hoy</div>
            <div class="text-2xl font-black text-blue-600">₡{{ number_format($collectionsToday, 0) }}</div>
            <div class="mt-2 flex items-center text-xs text-blue-500">
                <span class="font-bold">Recabado en campo</span>
            </div>
        </div>

        {{-- Route Coverage --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="text-sm font-medium text-gray-500 mb-1">Cobertura de Ruta</div>
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span
                            class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                            {{ $routeCoverage }}%
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-blue-600">
                            {{ $visitedCount }}/{{ $totalRuta }}
                        </span>
                    </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-100">
                    <div style="width:{{ $routeCoverage }}%"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500">
                    </div>
                </div>
            </div>
        </div>

        {{-- Critical Stock Alert --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="text-sm font-medium text-gray-500 mb-1">Alertas de Stock</div>
            <div class="text-2xl font-black {{ $lowStockProducts->count() > 0 ? 'text-red-500' : 'text-green-500' }}">
                {{ $lowStockProducts->count() }}
            </div>
            <div class="mt-2 text-xs text-gray-400">Productos agotándose</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Low Stock Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                <h4 class="font-bold text-gray-700">Inventario Crítico</h4>
                <a href="{{ route('products.index') }}" class="text-xs text-blue-600 font-bold hover:underline">Ver
                    todo</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($lowStockProducts as $prod)
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                        <div>
                            <div class="text-sm font-bold text-gray-800">{{ $prod->name }}</div>
                            <div class="text-[10px] text-gray-500 uppercase tracking-wider">SKU: {{ $prod->sku }}</div>
                        </div>
                        <div class="text-right">
                            <span
                                class="px-2 py-1 rounded text-xs font-bold {{ $prod->stock <= 0 ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600' }}">
                                Stock: {{ $prod->stock }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 italic">Todo el inventario está al día.</div>
                @endforelse
            </div>
        </div>

        {{-- Portfolio Analysis / Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <span class="mr-2">📈</span> Análisis de Cartera
            </h4>
            <p class="text-gray-600 text-sm mb-6 leading-relaxed">
                El saldo acumulado en Cuentas por Cobrar de todos los clientes es de:
                <span class="block text-3xl font-black text-indigo-600 mt-2">
                    ₡{{ number_format(\App\Models\Client::sum('current_balance'), 0) }}
                </span>
                <span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mt-1 block">
                    Total deuda activa en calle
                </span>
            </p>
            <div class="flex space-x-3">
                <button
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold transition flex-1">
                    Exportar Ruta
                </button>
                <button
                    class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg text-sm font-bold transition flex-1 border border-indigo-100">
                    Cierre de Caja
                </button>
            </div>
        </div>
    </div>
</div>