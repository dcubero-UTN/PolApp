<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Gestión de Vendedores</h2>
        <a href="{{ route('users.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-blue-700 transition">
            + Nuevo Vendedor
        </a>
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

    <div class="mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o correo..."
            class="w-full border rounded-xl px-4 py-3 shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-xl">
        <ul class="divide-y divide-gray-200">
            @forelse($users as $user)
                <li class="p-4 flex justify-between items-center hover:bg-gray-50 transition">
                    <div>
                        <div class="text-lg font-bold text-gray-800">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('users.edit', $user) }}"
                            class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase px-3 py-2 bg-indigo-50 rounded-lg transition">
                            Editar
                        </a>
                        <button wire:click="confirmDelete({{ $user->id }})"
                            class="text-red-600 hover:text-red-900 font-bold text-xs uppercase px-3 py-2 bg-red-50 rounded-lg transition">
                            Eliminar
                        </button>
                    </div>
                </li>
            @empty
                <li class="p-8 text-center text-gray-500 italic">
                    No se encontraron vendedores registrados.
                </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
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
                    <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tighter mb-2">¿Eliminar Usuario?</h3>
                    <p class="text-gray-500 text-sm font-medium mb-8">Esta acción borrará permanentemente la cuenta de este
                        vendedor. No se puede deshacer si tiene clientes asignados o ventas registradas.</p>

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