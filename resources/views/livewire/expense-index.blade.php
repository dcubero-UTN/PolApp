<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">
        {{-- Header & Stats --}}
        <div class="bg-gray-50 p-8 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <h2 class="text-3xl font-black text-gray-800 uppercase tracking-tighter flex items-center">
                        <span class="mr-3 text-red-500">📊</span> Control de Gastos
                    </h2>
                    <p class="text-gray-400 text-sm font-bold uppercase tracking-widest mt-1">Historial y Aprobaciones</p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('expenses.create') }}" 
                        class="bg-red-600 text-white px-8 py-3 rounded-2xl text-sm font-black shadow-lg hover:bg-red-700 transition transform hover:-translate-y-1 active:scale-95 uppercase tracking-wider">
                        + Registrar Gasto
                    </a>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" wire:model.live="search" placeholder="Buscar por concepto, proveedor..." 
                    class="w-full bg-white border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 font-medium">
                
                <select wire:model.live="status_filter" 
                    class="w-full bg-white border-gray-200 rounded-2xl shadow-sm focus:ring-red-500 focus:border-red-500 font-bold text-gray-600">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">⏳ Pendientes</option>
                    <option value="aprobado">✅ Aprobados</option>
                    <option value="rechazado">❌ Rechazados</option>
                </select>

                <div class="flex items-center space-x-2 bg-white px-4 py-2 rounded-2xl border border-gray-100 shadow-sm">
                    <span class="text-[10px] font-black text-gray-400 uppercase">Vista:</span>
                    <span class="text-xs font-black {{ auth()->user()->hasRole('admin') ? 'text-indigo-600' : 'text-orange-600' }} uppercase">
                        {{ auth()->user()->hasRole('admin') ? 'Global Administrador' : 'Mis Gastos Personales' }}
                    </span>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="mx-8 mt-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm relative animate-in fade-out duration-1000 delay-[4000ms]">
                <p class="text-sm font-bold text-green-700">{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                class="mx-8 mt-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm relative animate-in fade-out duration-1000 delay-[7000ms]">
                <p class="text-sm font-bold text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto p-4 sm:p-8">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100">
                        <th class="px-4 py-4">Fecha</th>
                        <th class="px-4 py-4">Solicitante</th>
                        <th class="px-4 py-4">Gasto / Categoría</th>
                        <th class="px-4 py-4">Importe</th>
                        <th class="px-4 py-4">Estado</th>
                        <th class="px-4 py-4">Reembolso</th>
                        <th class="px-4 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-gray-50/50 transition group">
                            <td class="px-4 py-6">
                                <p class="text-sm font-black text-gray-800">{{ $expense->date->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-4 py-6">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-black text-[10px] mr-3">
                                        {{ substr($expense->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-700 leading-tight">{{ $expense->user->name }}</p>
                                        <p class="text-[9px] text-gray-400 uppercase font-black">{{ $expense->user->roles->first()->name ?? 'Vendedor' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-6">
                                <p class="text-sm font-bold text-gray-800 leading-tight">{{ $expense->concept }}</p>
                                <div class="flex items-center mt-1">
                                    <span class="text-[9px] font-black uppercase text-red-500 bg-red-50 px-2 py-0.5 rounded mr-2">{{ $expense->category }}</span>
                                    <span class="text-[10px] text-gray-400 font-medium">{{ $expense->provider }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-6">
                                <p class="text-base font-black text-gray-900 tracking-tight">₡{{ number_format($expense->amount, 2) }}</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase">{{ $expense->payment_method }}</p>
                            </td>
                            <td class="px-4 py-6">
                                @php
                                    $statusClasses = [
                                        'pendiente' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                        'aprobado' => 'bg-green-100 text-green-700 border-green-200',
                                        'rechazado' => 'bg-red-100 text-red-700 border-red-200',
                                    ];
                                @endphp
                                <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full border {{ $statusClasses[$expense->status] }}">
                                    {{ $expense->status }}
                                </span>
                            </td>
                            <td class="px-4 py-6">
                                @if($expense->reimbursed)
                                    <span class="flex items-center text-blue-600 text-[10px] font-black uppercase">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" /></svg>
                                        Saldado
                                    </span>
                                @else
                                    <span class="text-gray-300 text-[10px] font-black uppercase">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-6 text-right space-x-2">
                                <div class="flex items-center justify-end gap-2">
                                    @if($expense->attachment_path)
                                        <button wire:click="openImage('{{ asset('storage/' . $expense->attachment_path) }}')" 
                                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-red-100 hover:text-red-600 transition shadow-sm" title="Ver Comprobante">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                    @endif

                                    @if($expense->status === 'pendiente')
                                        <a href="{{ route('expenses.edit', $expense->id) }}" 
                                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-indigo-100 hover:text-indigo-600 transition shadow-sm" title="Editar Gasto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        <button wire:click="confirmDelete({{ $expense->id }})" 
                                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-red-500 hover:text-white transition shadow-sm" title="Eliminar Gasto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endif

                                    @if(auth()->user()->hasRole('admin'))
                                        <div class="flex items-center border-l border-gray-100 pl-2 gap-2">
                                            @if($expense->status === 'pendiente')
                                                <button wire:click="approve({{ $expense->id }})" class="w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-600 hover:text-white transition shadow-sm" title="Aprobar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                                <button wire:click="reject({{ $expense->id }})" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm" title="Rechazar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            @endif
                                            
                                            <button wire:click="toggleReimbursed({{ $expense->id }})" 
                                                class="w-8 h-8 rounded-lg {{ $expense->reimbursed ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-600' }} hover:opacity-80 transition shadow-sm" title="Marcar como Reembolsado">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-6xl mb-6 grayscale opacity-20">🧾</span>
                                    <p class="text-gray-400 font-black uppercase tracking-widest text-sm">No se encontraron egresos</p>
                                    <p class="text-gray-300 text-xs mt-2 italic">Ajuste los filtros o registre un nuevo gasto para comenzar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100">
            {{ $expenses->links() }}
        </div>
    </div>

    {{-- Image Viewer Modal --}}
    @if($showImageModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-sm" wire:click="closeImageModal"></div>

                <div class="relative align-middle bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-lg w-full">
                    <div class="absolute top-4 right-4 z-10">
                        <button wire:click="closeImageModal" class="bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-80 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <img src="{{ $selectedImageUrl }}" class="w-full h-auto object-cover max-h-[80vh]">
                    <div class="p-6 bg-white flex justify-between items-center">
                        <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Comprobante Digital</span>
                        <a href="{{ $selectedImageUrl }}" target="_blank" class="text-red-500 font-bold text-xs hover:underline uppercase">Descargar Original</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-[70] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" wire:click="cancelDelete"></div>

                <div class="relative align-middle bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full p-8 text-center">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6">
                        <svg class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-800 uppercase tracking-tighter mb-2">¿Eliminar Gasto?</h3>
                    <p class="text-gray-500 text-sm font-medium mb-8">Esta acción borrará permanentemente la línea de gasto y su comprobante asociado. No se puede deshacer.</p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <button wire:click="cancelDelete" class="px-6 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition">
                            No, Cancelar
                        </button>
                        <button wire:click="deleteConfirmed" class="px-6 py-4 bg-red-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-red-200 hover:bg-red-700 transition">
                            Sí, Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
