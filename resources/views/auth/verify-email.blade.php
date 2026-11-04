<x-guest-layout>
    <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900">Confirma tu correo</h3>
        <p class="mt-1 text-sm text-gray-600">
            Te enviamos un enlace a <span class="font-semibold">{{ auth()->user()->email }}</span>.
            Ábrelo para confirmar tu correo y poder comprar. Si no lo ves, revisa tu carpeta de spam.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-green-600">
            Te enviamos un nuevo enlace de verificación.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#f5a623] px-4 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-[#f5a623] focus:ring-offset-2">
            Reenviar correo de verificación
        </button>
    </form>

    <div class="mt-5 flex items-center justify-between">
        <a class="text-sm font-semibold text-[#bd7710] transition hover:text-[#f5a623]" href="{{ route('catalog.index') }}">
            Seguir viendo productos
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm text-gray-600 underline transition hover:text-gray-900">
                Cerrar sesión
            </button>
        </form>
    </div>
</x-guest-layout>
