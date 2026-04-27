<nav x-data="{ open: false }" class="bg-cultura-700 dark:bg-gray-900 border-b border-cultura-800 shadow-lg">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                    <span class="ml-3 text-white font-bold text-lg hidden md:block">CulturaGest</span>
                </div>

                <!-- Navigation Links (Módulos Operativos) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white hover:text-acento-principal">
                        {{ __('Panel Principal') }}
                    </x-nav-link>

                    <!-- Acciones Operativas: Menú de Módulos -->
                    <x-nav-link :href="route('activos.index')" :active="request()->routeIs('activos.*')" class="text-white hover:text-acento-principal">
                        {{ __('Inventario') }}
                    </x-nav-link>

                    <x-nav-link :href="route('prestamos.index')" :active="request()->routeIs('prestamos.*')" class="text-white hover:text-acento-principal">
                        {{ __('Préstamos') }}
                    </x-nav-link>

                    <x-nav-link :href="route('mantenimientos.index')" :active="request()->routeIs('mantenimientos.*')" class="text-white hover:text-acento-principal">
                        {{ __('Mantenimiento') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Botón de Préstamo Rápido y Configuración -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <!-- Botón de Acción Principal: Nuevo Préstamo -->
                <a href="{{ route('prestamos.create') }}" class="inline-flex items-center px-4 py-2 bg-acento-principal border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-acento-hover focus:bg-acento-hover active:bg-acento-hover focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('Nuevo Préstamo') }}
                </a>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-cultura-800 hover:bg-cultura-600 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-cultura-600 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-cultura-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-white">
                {{ __('Panel Principal') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('activos.index')" :active="request()->routeIs('activos.*')" class="text-white">
                {{ __('Inventario') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('prestamos.create')" class="bg-acento-principal text-white font-bold">
                {{ __('+ Nuevo Préstamo') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings -->
        <div class="pt-4 pb-1 border-t border-cultura-600">
            <div class="px-4 text-white">
                <div class="font-medium text-base">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-cultura-300">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-white">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" class="text-white"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
