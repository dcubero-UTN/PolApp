<div class="p-6 bg-white shadow sm:rounded-lg">
    <form wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Info Básica --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Producto</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">SKU (Código Único) <span
                            class="text-red-500">*</span></label>
                    <input wire:model="sku" type="text" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                    @error('sku') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nombre del Artículo <span
                            class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <textarea wire:model="description" rows="3"
                        class="mt-1 block w-full border rounded-md shadow-sm p-2"></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Imagen del Producto</label>
                    @if ($current_image_path && !$image)
                        <img src="{{ Storage::url($current_image_path) }}" class="h-32 w-32 object-cover rounded mb-2">
                    @endif
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}" class="h-32 w-32 object-cover rounded mb-2">
                    @endif
                    <input wire:model="image" type="file"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="image" class="text-sm text-blue-500 mt-1">Subiendo imagen...</div>
                </div>
            </div>

            {{-- Finanzas e Inventario --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Finanzas e Inventario</h3>

                <div class="grid grid-cols-2 gap-4">
                    @role('admin')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Costo Adquisición (Admin) <span
                                class="text-red-500">*</span></label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">₡</span>
                            </div>
                            <input wire:model="cost_price" type="number" step="0.01"
                                class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('cost_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    @endrole

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Precio Venta <span
                                class="text-red-500">*</span></label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">₡</span>
                            </div>
                            <input wire:model="sale_price" type="number" step="0.01"
                                class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                        @error('sale_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if(!empty($cost_price) && !empty($sale_price) && auth()->user()->hasRole('admin'))
                    <div class="mb-4 text-sm text-gray-600">
                        Utilidad Proyectada: <span
                            class="font-bold text-green-600">₡{{ number_format($sale_price - $cost_price, 2) }}</span>
                    </div>
                @endif

                <div class="border-t border-gray-200 my-4 py-4">
                    <h4 class="font-medium text-gray-900 mb-2">Control de Stock</h4>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Stock Actual <span
                                    class="text-red-500">*</span></label>
                            <input wire:model="stock" type="number" {{ $product->exists ? 'readonly' : '' }}
                                class="mt-1 block w-full border rounded-md shadow-sm p-2 {{ $product->exists ? 'bg-gray-100 cursor-not-allowed text-gray-500' : '' }}">
                            @if($product->exists)
                                <span class="text-[10px] text-gray-400 font-bold uppercase">El stock se actualiza mediante
                                    compras</span>
                            @endif
                            @error('stock') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Alerta Mínima</label>
                            <input wire:model="min_stock_alert" type="number"
                                class="mt-1 block w-full border rounded-md shadow-sm p-2">
                            @error('min_stock_alert') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('products.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancelar</a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded shadow">
                Guardar Producto
            </button>
        </div>
    </form>
</div>