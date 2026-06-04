<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-ocre-400 bg-cantera-700/95 shadow-lg shadow-cantera-900/25 backdrop-blur">
    <div class="module-page-shell">
        <div class="grid h-16 grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3">
            <div class="min-w-0 flex items-center">
                <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <x-application-logo class="block h-9 w-auto shrink-0 fill-current text-hueso-50" />
                    <span class="hidden min-w-0 flex-col leading-tight md:flex">
                        <span class="text-base font-black text-hueso-50">CulturaGest</span>
                        <span class="text-xs font-bold uppercase tracking-widest text-ocre-200">Casa de la Cultura Cuilápam</span>
                    </span>
                </a>
            </div>

            <div class="hidden justify-self-end sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 rounded-lg border border-ocre-300/60 bg-cantera-800 px-3 py-2 text-sm font-bold leading-4 text-hueso-50 transition duration-150 hover:bg-cantera-700 focus:outline-none focus:ring-2 focus:ring-ocre-300">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-ocre-400 text-xs font-black text-anil-900">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="max-w-44 truncate">{{ Auth::user()->name }}</span>
                            <span class="ms-1">
                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center justify-self-end sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-lg border border-ocre-300/40 p-2 text-hueso-50 transition duration-150 ease-in-out hover:bg-cantera-800 focus:outline-none focus:ring-2 focus:ring-ocre-300" aria-label="Abrir menú">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{ 'block': open, 'hidden': ! open }" class="hidden border-t border-ocre-400/40 bg-cantera-700/95 shadow-xl shadow-cantera-900/20 sm:hidden">
        <div class="module-page-shell py-4">
            <div class="rounded-xl border border-ocre-300/40 bg-cantera-800 p-4 text-hueso-50">
                <div class="text-base font-black">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-ocre-200">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-hueso-50">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" class="text-hueso-50"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
