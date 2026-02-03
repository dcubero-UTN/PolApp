<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Role Information --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Información de Rol') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Tu rol actual en el sistema.') }}
                        </p>
                    </header>

                    <div class="mt-6">
                        <div class="flex items-center space-x-3">
                            <label class="text-sm font-medium text-gray-700">Rol:</label>
                            @if(Auth::user()->hasRole('admin'))
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">
                                    👑 Administrador
                                </span>
                            @elseif(Auth::user()->hasRole('vendedor'))
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                    👤 Vendedor
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-semibold">
                                    Usuario
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            Los roles determinan qué funciones del sistema puedes acceder.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>