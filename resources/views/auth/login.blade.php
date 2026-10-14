<x-guest-layout>
    <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900">Inicia sesión</h3>
        <p class="mt-1 text-sm text-gray-600">Nos da gusto tenerte de vuelta.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl bg-white px-3.5 py-3 text-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nombre@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-2">
                <x-input-label for="password" value="Contraseña" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-[#bd7710] transition hover:text-[#f5a623]" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                @endif
            </div>
            <x-text-input id="password" class="mt-2 block w-full rounded-xl bg-white px-3.5 py-3 text-sm" type="password" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#bd7710] shadow-sm focus:ring-[#f5a623]" name="remember">
            <span class="text-sm text-gray-600">Mantener mi sesión iniciada</span>
        </label>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#f5a623] px-4 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-[#f5a623] focus:ring-offset-2">
            Entrar a mi cuenta
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 border-t border-gray-200 pt-5 text-center text-sm text-gray-600">
            ¿Aún no tienes cuenta?
            <a href="{{ route('register') }}" class="ml-1 font-bold text-[#bd7710] transition hover:text-[#f5a623]">Regístrate</a>
        </p>
    @endif
</x-guest-layout>
