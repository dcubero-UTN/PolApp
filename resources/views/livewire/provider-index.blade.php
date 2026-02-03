<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-extrabold text-gray-800 flex items-center">
                <span class="mr-2 text-indigo-500">🚛</span> Gestión de Proveedores
            </h2>
            <button wire:click="openModal"
                class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-black shadow-lg hover:bg-indigo-700 transition uppercase">
                + Nuevo Proveedor
            </button>
        </div>

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-r relative animate-in fade-out duration-1000 delay-[4000ms]"
                role="alert">
                <p>{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded-r relative animate-in fade-out duration-1000 delay-[7000ms]"
                role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="mb-6">
            <input type="text" wire:model.live="search" placeholder="Buscar por nombre o contacto..."
                class="w-full md:w-1/3 border-gray-200 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="overflow-x-auto bg-white border border-gray-100 rounded-2xl shadow-sm">
            <table class="w-full text-left">
                <thead class="bg-gray-50 text-xs font-black text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Empresa</th>
                        <th class="px-6 py-4">Contacto</th>
                        <th class="px-6 py-4">Teléfono</th>
                        <th class="px-6 py-4 text-center">Compras</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($providers as $provider)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-sm font-bold text-gray-800">
                                {{ $provider->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $provider->contact_name ?? '---' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium">
                                {{ $provider->phone ?? '---' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-black px-2 py-1 rounded">
                                    {{ $provider->purchases->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button wire:click="openModal({{ $provider->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase">Editar</button>
                                <button wire:click="confirmDelete({{ $provider->id }})"
                                    class="text-red-600 hover:text-red-900 font-bold text-xs uppercase">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium">
                                No se encontraron proveedores.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $providers->links() }}
        </div>
    </div>

    {{-- Provider Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="bg-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-black text-white uppercase tracking-wider">
                            {{ $editMode ? '✏️ Editar' : '🚛 Nuevo' }} Proveedor
                        </h3>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="px-6 py-4 space-y-4 bg-white">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre Comercial <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model="name"
                                    class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre de
                                    Contacto</label>
                                <input type="text" wire:model="contact_name"
                                    class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Teléfono</label>
                                <input type="text" wire:model="phone"
                                    class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Dirección</label>
                                <textarea wire:model="address" rows="3"
                                    class="w-full border-gray-200 rounded-xl shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-bold text-gray-500 uppercase hover:text-gray-700 transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-black shadow-lg hover:bg-indigo-700 transition uppercase">
                                {{ $editMode ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

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
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tighter mb-2">¿Eliminar Proveedor?</h3>
                    <p class="text-gray-500 text-sm font-medium mb-8">Esta acción borrará permanentemente al proveedor. No
                        se puede deshacer si no existen compras relacionadas.</p>

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