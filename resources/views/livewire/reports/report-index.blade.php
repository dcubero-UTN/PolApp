<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-black text-gray-800 tracking-tight uppercase">Centro de Reportes</h2>
        <p class="text-gray-500 font-bold uppercase text-[10px] tracking-[0.2em] mt-1">Análisis y Estadísticas
            Operativas</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {{-- Report Card: Returns --}}
        <a href="{{ route('reports.returns') }}"
            class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 hover:border-orange-300 transition-all group relative overflow-hidden">
            <div
                class="absolute -top-10 -right-10 w-32 h-32 bg-orange-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700">
            </div>

            <div
                class="w-16 h-16 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-3xl mb-6 relative z-10">
                ↩️
            </div>

            <h3 class="text-xl font-black text-gray-800 mb-2 relative z-10">Devoluciones y Mermas</h3>
            <p class="text-sm text-gray-500 leading-relaxed relative z-10 italic">
                Monitoreo de retornos, productos dañados y recuperación de inventario.
            </p>

            <div
                class="mt-8 flex items-center text-[10px] font-black uppercase tracking-widest text-orange-600 group-hover:translate-x-2 transition-transform">
                EXAMINAR REPORTE <span class="ml-2">→</span>
            </div>
        </a>

        {{-- Report Card: Accounts Payable --}}
        <a href="{{ route('reports.accounts-payable') }}"
            class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 hover:border-red-300 transition-all group relative overflow-hidden">
            <div
                class="absolute -top-10 -right-10 w-32 h-32 bg-red-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700">
            </div>

            <div
                class="w-16 h-16 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-3xl mb-6 relative z-10">
                💸
            </div>

            <h3 class="text-xl font-black text-gray-800 mb-2 relative z-10">Cuentas por Pagar</h3>
            <p class="text-sm text-gray-500 leading-relaxed relative z-10 italic">
                Control de deudas con distribuidores, vencimientos y flujo de pagos.
            </p>

            <div
                class="mt-8 flex items-center text-[10px] font-black uppercase tracking-widest text-red-600 group-hover:translate-x-2 transition-transform">
                GESTIONAR PASIVOS <span class="ml-2">→</span>
            </div>
        </a>

        {{-- Report Card: Finance & Profitability --}}
        <a href="{{ route('reports.finance') }}"
            class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-gray-100 border border-gray-100 hover:border-blue-300 transition-all group relative overflow-hidden">
            <div
                class="absolute -top-10 -right-10 w-32 h-32 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700">
            </div>

            <div
                class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-6 relative z-10">
                📈
            </div>

            <h3 class="text-xl font-black text-gray-800 mb-2 relative z-10">Ventas y Rentabilidad</h3>
            <p class="text-sm text-gray-500 leading-relaxed relative z-10 italic">
                Análisis de utilidad neta, márgenes por producto y desempeño comercial.
            </p>

            <div
                class="mt-8 flex items-center text-[10px] font-black uppercase tracking-widest text-blue-600 group-hover:translate-x-2 transition-transform">
                VER ESTADÍSTICAS <span class="ml-2">→</span>
            </div>
        </a>

        <div
            class="bg-gray-50 p-8 rounded-[2.5rem] border border-dashed border-gray-200 flex flex-col items-center justify-center text-center opacity-60">
            <div
                class="w-16 h-16 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center text-3xl mb-4 grayscale">
                🗺️
            </div>
            <h3 class="text-lg font-bold text-gray-400">Eficiencia de Rutas</h3>
            <p class="text-xs text-gray-400 mt-1 uppercase font-black tracking-tighter">Próximamente</p>
        </div>
    </div>
</div>