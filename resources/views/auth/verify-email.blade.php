<x-guest-layout>
    <div class="mb-4 text-sm text-cantera-700 dark:text-cantera-500">
        {{ __('Gracias por registrarte. Antes de continuar, verifica tu correo electrónico con el enlace que acabamos de enviarte. Si no lo recibiste, podemos enviarte otro.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-cantera-600 dark:text-cantera-400">
            {{ __('Se envió un nuevo enlace de verificación al correo electrónico que usaste durante el registro.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Reenviar correo de verificación') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-cantera-700 dark:text-cantera-500 hover:text-anil-900 dark:hover:text-hueso-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-anil-500 dark:focus:ring-offset-anil-900">
                {{ __('Cerrar sesión') }}
            </button>
        </form>
    </div>
</x-guest-layout>
