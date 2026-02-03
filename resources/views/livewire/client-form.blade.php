<div class="p-6 bg-white shadow sm:rounded-lg">
    <form wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Datos Personales --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos Personales</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Nombre Completo <span
                            class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Celular (Principal) <span
                            class="text-red-500">*</span></label>
                    <input wire:model="phone_primary" type="text"
                        class="mt-1 block w-full border rounded-md shadow-sm p-2">
                    @error('phone_primary') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Teléfono Fijo / Secundario</label>
                    <input wire:model="phone_secondary" type="text"
                        class="mt-1 block w-full border rounded-md shadow-sm p-2">
                    @error('phone_secondary') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input wire:model="email" type="email" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                @if($sellers->isNotEmpty())
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Vendedor Asignado <span
                                class="text-red-500">*</span></label>
                        <select wire:model="user_id" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                            <option value="">Seleccione...</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            {{-- Localización y Cobro --}}
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Localización y Logística</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Dirección (Señas) <span
                            class="text-red-500">*</span></label>
                    <textarea wire:model="address_details" rows="4"
                        class="mt-1 block w-full border rounded-md shadow-sm p-2"
                        placeholder="Ej: De la pulpería 200 sur, portón negro..."></textarea>
                    @error('address_details') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Frecuencia <span
                                class="text-red-500">*</span></label>
                        <select wire:model.live="collection_frequency"
                            class="mt-1 block w-full border rounded-md shadow-sm p-2">
                            <option value="">Seleccione...</option>
                            @foreach(['Diario', 'Semanal', 'Quincenal', 'Mensual'] as $freq)
                                <option value="{{ $freq }}">{{ $freq }}</option>
                            @endforeach
                        </select>
                        @error('collection_frequency') <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    @if($collection_frequency !== 'Diario')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                @if($collection_frequency === 'Semanal')
                                    Día de Cobro
                                @elseif($collection_frequency === 'Quincenal')
                                    Periodo (15/30 o 16/31)
                                @elseif($collection_frequency === 'Mensual')
                                    Día del Mes
                                @endif
                                <span class="text-red-500">*</span>
                            </label>

                            @if($collection_frequency === 'Semanal')
                                <select wire:model="collection_day" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                                    <option value="">Seleccione...</option>
                                    @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            @elseif($collection_frequency === 'Quincenal')
                                <select wire:model="collection_day" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                                    <option value="">Seleccione periodo...</option>
                                    <option value="15/30">Días 15 y 30 de cada mes</option>
                                    <option value="16/31">Días 16 y 31 de cada mes</option>
                                </select>
                            @elseif($collection_frequency === 'Mensual')
                                <select wire:model="collection_day" class="mt-1 block w-full border rounded-md shadow-sm p-2">
                                    <option value="">Seleccione día...</option>
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}">Día {{ $i }}</option>
                                    @endfor
                                </select>
                            @endif
                            @error('collection_day') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Hora Sugerida</label>
                    <input wire:model="hora_cobro" type="time"
                        class="mt-1 block w-full border rounded-md shadow-sm p-2">
                    @error('hora_cobro') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>


            </div>
        </div>

        <div class="flex justify-end mt-6">
            <a href="{{ route('clients.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Cancelar</a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded shadow">
                Guardar Cliente
            </button>
        </div>
    </form>
</div>