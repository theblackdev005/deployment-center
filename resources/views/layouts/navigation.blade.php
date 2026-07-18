<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex min-w-0 items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-3 text-slate-950">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-600 text-sm font-bold text-white">DC</span>
                    <span class="hidden text-base font-semibold sm:block">Deploy Center</span>
                </a>

                <div class="hidden items-center gap-1 md:flex">
                    @foreach ([
                        ['route' => 'dashboard', 'label' => 'Vue d’ensemble', 'match' => 'dashboard'],
                        ['route' => 'deployments.index', 'label' => 'Déploiements', 'match' => 'deployments.*'],
                        ['route' => 'setup.index', 'label' => 'Configuration', 'match' => 'setup.*'],
                    ] as $item)
                        <a href="{{ route($item['route']) }}"
                           class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($item['match']) ? 'bg-slate-100 text-slate-950' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('deployments.create') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Nouveau déploiement
                </a>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            {{ Auth::user()->name }}
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Mon compte</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Se déconnecter
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button @click="open = !open" class="flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 text-slate-700 md:hidden" aria-label="Ouvrir le menu">
                <span x-show="!open" class="text-xl">☰</span>
                <span x-show="open" class="text-xl">×</span>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="border-t border-slate-200 px-4 py-3 md:hidden">
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-slate-700">Vue d’ensemble</a>
            <a href="{{ route('deployments.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-slate-700">Déploiements</a>
            <a href="{{ route('setup.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-slate-700">Configuration</a>
            <a href="{{ route('deployments.create') }}" class="mt-2 block rounded-md bg-emerald-600 px-3 py-2 text-center text-sm font-semibold text-white">Nouveau déploiement</a>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-slate-200 pt-3">
            @csrf
            <button class="w-full px-3 py-2 text-left text-sm font-medium text-slate-600">Se déconnecter</button>
        </form>
    </div>
</nav>
