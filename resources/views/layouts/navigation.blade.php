<div x-data="{ open: false }">
    <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-slate-950">
            <x-app-brand />
        </a>
        <button type="button" @click="open = !open" class="flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 text-slate-700" aria-label="Ouvrir la navigation">
            <i data-lucide="menu" class="h-5 w-5" aria-hidden="true"></i>
        </button>
    </div>

    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-30 bg-slate-950/30 lg:hidden"></div>

    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="flex h-16 items-center border-b border-slate-200 px-5">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-slate-950">
                <x-app-brand subtitle />
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-5">
            <p class="px-3 text-xs font-bold uppercase text-slate-400">Pilotage</p>
            <div class="mt-2 space-y-1">
                @foreach ([
                    ['route' => 'dashboard', 'label' => 'Vue d’ensemble', 'match' => 'dashboard', 'icon' => 'home'],
                    ['route' => 'deployments.index', 'label' => 'Déploiements', 'match' => 'deployments.*', 'icon' => 'history'],
                    ['route' => 'hostinger.index', 'label' => 'Parc Hostinger', 'match' => 'hostinger.*', 'icon' => 'globe-2'],
                    ['route' => 'clients-sites.index', 'label' => 'Clients et sites', 'match' => 'clients-sites.*', 'icon' => 'users'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="flex min-h-10 items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($item['match']) ? 'bg-[#f0edff] text-[#5b34d1]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <p class="mt-7 px-3 text-xs font-bold uppercase text-slate-400">Ressources</p>
            <div class="mt-2 space-y-1">
                @foreach ([
                    ['route' => 'projects.index', 'label' => 'Projets GitHub', 'match' => 'projects.*', 'icon' => 'folder-git-2'],
                    ['route' => 'servers.index', 'label' => 'Connexions de déploiement', 'match' => 'servers.*', 'icon' => 'server'],
                    ['route' => 'domains.index', 'label' => 'Domaines de déploiement', 'match' => 'domains.*', 'icon' => 'box'],
                ] as $item)
                    <a href="{{ route($item['route']) }}" class="flex min-h-10 items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold transition {{ request()->routeIs($item['match']) ? 'bg-[#f0edff] text-[#5b34d1]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <p class="mt-7 px-3 text-xs font-bold uppercase text-slate-400">Administration</p>
            <div class="mt-2">
                <a href="{{ route('setup.index') }}" class="flex min-h-10 items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('setup.*') ? 'bg-[#f0edff] text-[#5b34d1]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
                    <i data-lucide="settings-2" class="h-4 w-4" aria-hidden="true"></i>
                    Configuration
                </a>
            </div>
        </nav>

        <div class="border-t border-slate-200 p-3">
            <button
                type="button"
                x-data
                x-show="!$store.pwa.installed"
                @click="$store.pwa.install()"
                class="mb-1 flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-[#f0edff] hover:text-[#5b34d1]"
            >
                <i data-lucide="download" class="h-4 w-4" aria-hidden="true"></i>
                Installer l’application
            </button>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 hover:bg-slate-100">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                    <i data-lucide="user-round" class="h-4 w-4" aria-hidden="true"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-slate-500">Administrateur</p>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-red-50 hover:text-red-700">
                    <i data-lucide="log-out" class="h-4 w-4" aria-hidden="true"></i>
                    Se déconnecter
                </button>
            </form>
        </div>
    </aside>
</div>
