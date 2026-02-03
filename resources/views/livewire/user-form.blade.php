<div class="max-w-2xl mx-auto p-6">
    <div class="flex items-center space-x-4 mb-8">
        <a href="{{ route('users.index') }}"
            class="bg-gray-100 text-gray-500 p-2 rounded-full hover:bg-gray-200 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
        </a>
        <h2 class="text-3xl font-black text-gray-800">{{ $is_editing ? 'Editar Vendedor' : 'Nuevo Vendedor' }}</h2>
    </div>

    <form wire:submit="save" class="bg-white rounded-3xl shadow-xl p-8 space-y-6 border border-gray-100">
        {{-- Name --}}
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nombre Completo</label>
            <input wire:model="name" type="text"
                class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring-blue-500 focus:border-blue-500 transition-all"
                placeholder="Ej. Juan Pérez">
            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Correo
                Electrónico</label>
            <input wire:model="email" type="email"
                class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring-blue-500 focus:border-blue-500 transition-all"
                placeholder="juan@ejemplo.com">
            @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Password --}}
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                    {{ $is_editing ? 'Cambiar Contraseña' : 'Contraseña' }}
                </label>
                <input wire:model="password" type="password"
                    class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring-blue-500 focus:border-blue-500 transition-all"
                    placeholder="••••••••">
                @if($is_editing)
                    <p class="text-[10px] text-gray-400 mt-1 italic">Dejar vacío para mantener la actual</p>
                @endif
                @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Confirmar
                    Contraseña</label>
                <input wire:model="password_confirmation" type="password"
                    class="w-full border-gray-200 rounded-xl px-4 py-3 focus:ring-blue-500 focus:border-blue-500 transition-all"
                    placeholder="••••••••">
            </div>
        </div>

        <div class="pt-4">
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl shadow-lg transition-all transform hover:scale-[1.01] active:scale-95 flex items-center justify-center">
                <svg wire:loading class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                {{ $is_editing ? 'ACTUALIZAR VENDEDOR' : 'CREAR VENDEDOR' }}
            </button>
        </div>
    </form>
</div>