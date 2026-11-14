<nav class="bg-brand-secondary text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <div class="flex items-center gap-6">
                <span class="font-bold text-xl">Nutri Admin 🍐</span>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="text-sm hover:underline {{ request()->routeIs('dashboard') ? 'font-bold underline' : '' }}">Leads</a>
                <a href="{{ route('admin.convites') }}" wire:navigate
                    class="text-sm hover:underline {{ request()->routeIs('admin.convites') ? 'font-bold underline' : '' }}">Convites</a>
            </div>
            <button wire:click="logout" class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1 rounded transition">
                Sair do Sistema
            </button>
        </div>
    </div>
</nav>
