<?php use App\Core\ViewHelper; ?>

<aside class="w-20 bg-gray-900 text-white flex flex-col items-center py-6 gap-6 shadow-2xl z-50 flex-shrink-0">
    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-900/50 mb-4">
        <i data-lucide="store" class="text-white w-6 h-6"></i>
    </div>
    
    <nav class="flex-1 w-full flex flex-col gap-4 px-2">
        <a href="pdv" class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 transition-all duration-200 group relative <?= ViewHelper::isActive('pdv') ?>">
            <i data-lucide="layout-dashboard" class="w-6 h-6 transition-transform duration-300 group-hover:scale-110"></i>
            <span class="text-[10px] font-medium opacity-80">Balcão</span>
        </a>

        <a href="mesas" class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 transition-all duration-200 group relative <?= ViewHelper::isActive('mesas') ?>">
            <i data-lucide="utensils-crossed" class="w-6 h-6 transition-transform duration-300 group-hover:scale-110"></i>
            <span class="text-[10px] font-medium opacity-80">Mesas</span>
        </a>

        <a href="produtos" class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 transition-all duration-200 group relative <?= ViewHelper::isActive('produtos') ?>">
            <i data-lucide="package" class="w-6 h-6 transition-transform duration-300 group-hover:scale-110"></i>
            <span class="text-[10px] font-medium opacity-80">Estoque</span>
        </a>

        <a href="categorias" class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center gap-1 transition-all duration-200 group relative <?= ViewHelper::isActive('categorias') ?>">
            <i data-lucide="tags" class="w-6 h-6 transition-transform duration-300 group-hover:scale-110"></i>
            <span class="text-[10px] font-medium opacity-80">Categ.</span>
        </a>
    </nav>

    <div class="w-full flex flex-col gap-4 px-2">
        <a href="../../admin" class="w-12 h-12 rounded-xl flex items-center justify-center text-gray-400 hover:bg-gray-800 hover:text-red-400 transition-all">
            <i data-lucide="log-out" class="w-6 h-6"></i>
        </a>
    </div>
</aside>
