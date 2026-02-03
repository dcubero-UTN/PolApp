<x-app-layout>

    <div class="pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Welcome Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-bold mb-2">Bienvenido, {{ Auth::user()->name }}! 👋</h3>
                    <p class="text-gray-600">Sistema de Gestión PolaApp</p>
                </div>
            </div>

            {{-- Navigation Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Clients Module --}}
                <a href="{{ route('clients.index') }}"
                    class="bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-lg p-6 transition-all transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                        </div>
                        <span class="text-3xl font-bold">👥</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Clientes</h3>
                    <p class="text-blue-100 text-sm">Gestiona tu cartera de clientes y rutas de cobro</p>
                </a>

                {{-- Sales Module --}}
                <a href="{{ route('sales.create') }}"
                    class="bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg shadow-lg p-6 transition-all transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                            </svg>
                        </div>
                        <span class="text-3xl font-bold">🛒</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Nueva Venta</h3>
                    <p class="text-green-100 text-sm">Registra ventas y gestiona créditos</p>
                </a>

                @role('admin')
                {{-- Products Module (Admin Only) --}}
                <a href="{{ route('products.index') }}"
                    class="bg-gradient-to-br from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white rounded-lg shadow-lg p-6 transition-all transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <span class="text-3xl font-bold">📦</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Inventario</h3>
                    <p class="text-purple-100 text-sm">Administra productos y stock</p>
                </a>
                @endrole

                {{-- Collections Module --}}
                <a href="{{ route('clients.index') }}"
                    class="bg-gradient-to-br from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white rounded-lg shadow-lg p-6 transition-all transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="text-3xl font-bold">💰</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Cobros</h3>
                    <p class="text-yellow-100 text-sm">Ruta diaria de cobros y pagos</p>
                </a>

                @role('admin')
                {{-- Reports Module (Admin Only) --}}
                <a href="{{ route('reports.index') }}"
                    class="bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg shadow-lg p-6 transition-all transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <span class="text-3xl font-bold">📊</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Reportes</h3>
                    <p class="text-red-100 text-sm">Estadísticas, Inventario y Liquidaciones</p>
                </a>
                @endrole

                {{-- Profile/Settings --}}
                <a href="{{ route('profile.edit') }}"
                    class="bg-gradient-to-br from-gray-700 to-gray-800 hover:from-gray-800 hover:to-gray-900 text-white rounded-lg shadow-lg p-6 transition-all transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white bg-opacity-20 rounded-full p-3">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="text-3xl font-bold">⚙️</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Configuración</h3>
                    <p class="text-gray-300 text-sm">Perfil y ajustes de cuenta</p>
                </a>

            </div>

            {{-- Administrative Only Sections --}}
            <div class="mt-12 space-y-12">
                {{-- Purchases Module --}}
                @role('admin')
                <section>
                    <h3 class="text-xl font-black text-gray-800 mb-6 flex items-center uppercase tracking-wider">
                        <span class="mr-2 text-orange-500">📥</span> Módulo de Compras
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <a href="{{ route('purchases.create') }}"
                            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-orange-300 transition group">
                            <div
                                class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <span class="text-2xl">➕</span>
                            </div>
                            <h4 class="font-bold text-gray-800">Nueva Compra</h4>
                            <p class="text-xs text-gray-500 mt-1">Registrar ingreso de mercancía</p>
                        </a>
                        <a href="{{ route('providers.index') }}"
                            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-orange-300 transition group">
                            <div
                                class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <span class="text-2xl">🚛</span>
                            </div>
                            <h4 class="font-bold text-gray-800">Proveedores</h4>
                            <p class="text-xs text-gray-500 mt-1">Gestión de contactos de suministro</p>
                        </a>
                        <a href="{{ route('purchases.index') }}"
                            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-orange-300 transition group">
                            <div
                                class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <span class="text-2xl">📋</span>
                            </div>
                            <h4 class="font-bold text-gray-800">Historial de Compras</h4>
                            <p class="text-xs text-gray-500 mt-1">Consulta de facturas recibidas</p>
                        </a>
                    </div>
                </section>
                @endrole

                {{-- Expenses Module (Visible for Admin and Seller) --}}
                <section>
                    <h3 class="text-xl font-black text-gray-800 mb-6 flex items-center uppercase tracking-wider">
                        <span class="mr-2 text-red-500">💸</span> Módulo de Gastos
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <a href="{{ route('expenses.create') }}"
                            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-red-300 transition group">
                            <div
                                class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <span class="text-2xl">🧾</span>
                            </div>
                            <h4 class="font-bold text-gray-800">Registrar Gasto</h4>
                            <p class="text-xs text-gray-500 mt-1">Control de egresos operativos</p>
                        </a>
                        <a href="{{ route('expenses.index') }}"
                            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-red-300 transition group">
                            <div
                                class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <span class="text-2xl">📋</span>
                            </div>
                            <h4 class="font-bold text-gray-800">Historial de Gastos</h4>
                            <p class="text-xs text-gray-500 mt-1">Consulta y estados de cuenta</p>
                        </a>
                        <a href="{{ route('expenses.index', ['status_filter' => 'pendiente']) }}"
                            class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-red-300 transition group">
                            <div
                                class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition">
                                <span class="text-2xl">⏳</span>
                            </div>
                            <h4 class="font-bold text-gray-800">Aprobaciones</h4>
                            <p class="text-xs text-gray-500 mt-1">Gastos pendientes por revisar</p>
                        </a>
                    </div>
                </section>
            </div>

            {{-- Administrative Metrics Component --}}
            @role('admin')
            <livewire:dashboard-admin />
            @endrole

        </div>
    </div>
</x-app-layout>