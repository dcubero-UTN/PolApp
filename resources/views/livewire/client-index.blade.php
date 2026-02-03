<div class="p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Clientes</h2>
        <a href="{{ route('clients.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Nuevo Cliente</a>
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

    <div class="mb-4 space-y-4">
        {{-- Route Progress Bar --}}
        @if($totalRoute > 0)
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 italic">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Progreso de la Ruta</span>
                    <span class="text-xs font-bold text-blue-600">{{ $completedRoute }}/{{ $totalRoute }} completadas</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                        style="width: {{ min(($completedRoute / max($totalRoute, 1)) * 100, 100) }}%"></div>
                </div>
            </div>
        @endif

        {{-- Search Bar & Filters --}}
        <div class="flex flex-col md:flex-row gap-3 items-center">
            <div class="relative w-full md:w-2/5">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre, celular..."
                    class="border border-gray-200 rounded-xl pl-10 pr-4 py-2 w-full shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
            </div>

            @role('admin')
            <div class="w-full md:w-1/4">
                <select wire:model.live="seller_id"
                    class="border border-gray-200 rounded-xl px-4 py-2 w-full bg-white shadow-sm focus:ring-2 focus:ring-blue-500 font-medium text-gray-700">
                    <option value="">Todos los vendedores</option>
                    @foreach($sellers as $seller)
                        <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                    @endforeach
                </select>
            </div>
            @endrole

            <div class="flex items-center bg-white border border-gray-200 rounded-xl px-4 py-2 shadow-sm shrink-0">
                <label for="with_balance" class="flex items-center cursor-pointer mb-0">
                    <div class="relative shrink-0">
                        <input id="with_balance" type="checkbox" wire:model.live="only_with_balance" class="sr-only">
                        <div
                            class="block bg-gray-100 w-8 h-5 rounded-full transition-colors {{ $only_with_balance ? 'bg-blue-600' : '' }}">
                        </div>
                        <div
                            class="dot absolute left-1 top-1 bg-white w-3 h-3 rounded-full transition-transform {{ $only_with_balance ? 'translate-x-3' : '' }}">
                        </div>
                    </div>
                    <span
                        class="ml-2 text-gray-600 font-black text-[10px] uppercase tracking-wider whitespace-nowrap select-none">
                        Solo con deuda
                    </span>
                </label>
            </div>
        </div>

        {{-- Days Tabs --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-2 overflow-x-auto pb-2" aria-label="Tabs">
                @foreach($days as $dayName => $dayAbbr)
                    <button wire:click="setDay('{{ $dayName }}')"
                        class="{{ $collection_day === $dayName ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                                                                        whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm flex-1 md:flex-none text-center transition-colors duration-200 ease-in-out">
                        <span class="md:hidden">{{ $dayAbbr }}</span>
                        <span class="hidden md:inline">{{ $dayName }}</span>
                    </button>
                @endforeach
                <button wire:click="setDay('')"
                    class="{{ $collection_day === '' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                    whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm flex-1 md:flex-none text-center transition-colors duration-200 ease-in-out">
                    Todo
                </button>
            </nav>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($clients->isEmpty() && empty($search))
            <div class="p-6 text-center text-gray-500">
                No hay clientes programados para esta ruta.
            </div>
        @endif
        <ul class="divide-y divide-gray-200">
            @forelse($clients as $client)
                <li wire:key="client-{{ $client->id }}"
                    class="p-4 transition {{ $client->is_completed ? 'bg-green-50/50 grayscale-[0.3]' : 'hover:bg-gray-50' }} flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        @if($client->is_completed)
                            <div class="bg-green-100 text-green-600 p-2 rounded-full shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('clients.show', $client) }}"
                                    class="text-lg font-bold {{ $client->is_completed ? 'text-green-700' : 'text-blue-600' }} hover:underline">{{ $client->name }}</a>
                                @if($client->hora_cobro)
                                    <span
                                        class="text-[10px] bg-gray-100 text-gray-500 px-1 rounded">{{ $client->hora_cobro }}</span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <div
                                    class="text-sm font-black {{ $client->current_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    ₡{{ number_format($client->current_balance, 0) }}
                                </div>
                                <div class="text-sm text-gray-400">•</div>
                                <div class="text-sm text-gray-500">{{ $client->address_details }}</div>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                @if($client->next_visit_date == date('Y-m-d'))
                                    <span
                                        class="bg-purple-100 text-purple-800 text-xs font-bold mr-2 px-2.5 py-0.5 rounded border border-purple-200">✨
                                        VISITA EXTRA</span>
                                @else
                                    <span
                                        class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">{{ $client->collection_day }}</span>
                                @endif
                                <span>{{ $client->phone_primary }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        @if($client->next_visit_notes)
                            <div class="group relative flex justify-center">
                                <span class="bg-gray-100 p-2 rounded-full cursor-help">📝</span>
                                <span
                                    class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs p-2 rounded shadow-lg w-48 z-10">
                                    {{ $client->next_visit_notes }}
                                </span>
                            </div>
                        @endif
                        <a href="tel:{{ $client->phone_primary }}"
                            class="bg-green-500 text-white p-2 rounded-full shadow hover:bg-green-600">
                            📞
                        </a>
                        <button wire:click="openPaymentModal({{ $client->id }})"
                            class="bg-green-600 text-white px-3 py-2 rounded-lg font-bold shadow hover:bg-green-700">
                            Registrar Abono
                        </button>
                        <a href="https://wa.me/506{{ preg_replace('/[^0-9]/', '', $client->phone_primary) }}"
                            target="_blank" class="bg-green-500 text-white p-2 rounded-lg shadow hover:bg-green-600">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                            </svg>
                        </a>
                    </div>
                </li>
            @empty
                <li class="p-4 text-center text-gray-500">No se encontraron clientes.</li>
            @endforelse
        </ul>
    </div>


    <div class="mt-4">
        {{ $clients->links() }}
    </div>
</div>