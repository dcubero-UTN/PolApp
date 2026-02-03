<div class="p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Inventario de Productos</h2>
        @role('admin')
        <div class="text-right">
            <span class="text-sm text-gray-600">Valor Total Bodega:</span>
            <span class="text-lg font-bold text-green-600">₡{{ number_format($totalValue, 2) }}</span>
        </div>
        @endrole
        @role('admin')
        <a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Nuevo Producto</a>
        @endrole
    </div>

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

    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o SKU..."
            class="border rounded px-4 py-2 w-full md:w-1/3">
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio
                        Venta</th>
                    @role('admin')
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilidad
                        Unit.</th>
                    @endrole
                    @role('admin')
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones
                    </th>
                    @endrole
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                    <tr class="@if($product->stock <= $product->min_stock_alert) bg-red-50 @endif">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($product->image_path)
                                    <img class="h-10 w-10 rounded-full mr-3 object-cover"
                                        src="{{ Storage::url($product->image_path) }}" alt="">
                                @else
                                    <div
                                        class="h-10 w-10 rounded-full bg-gray-200 mr-3 flex items-center justify-center text-gray-400">
                                        📷</div>
                                @endif
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            </div>
                            @if($product->stock <= $product->min_stock_alert)
                                <span class="text-xs text-red-600 font-bold ml-13">⚠️ Stock Bajo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $product->stock }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₡{{ number_format($product->sale_price, 2) }}</td>
                        @role('admin')
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ₡{{ number_format($product->cost_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            ₡{{ number_format($product->sale_price - $product->cost_price, 2) }}</td>
                        @endrole
                        @role('admin')
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('products.edit', $product) }}"
                                class="text-indigo-600 hover:text-indigo-900 mr-3 font-bold uppercase text-xs">Editar</a>
                            <button wire:click="confirmDelete({{ $product->id }})"
                                class="text-red-600 hover:text-red-900 font-bold uppercase text-xs">Eliminar</button>
                        </td>
                        @endrole
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay productos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
                    wire:click="cancelDelete"></div>

                <div
                    class="relative align-middle bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6">
                        <svg class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tighter mb-2">¿Eliminar Producto?</h3>
                    <p class="text-gray-500 text-sm font-medium mb-8">Esta acción borrará permanentemente el producto del
                        inventario. No se puede deshacer si tiene historial de transacciones.</p>

                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="cancelDelete"
                            class="px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                            No, Cancelar
                        </button>
                        <button wire:click="deleteConfirmed"
                            class="px-6 py-4 bg-red-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-red-200 hover:bg-red-700 transition">
                            Sí, Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>