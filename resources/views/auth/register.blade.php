<x-guest-layout>
    <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900">Crea tu cuenta</h3>
        <p class="mt-1 text-sm text-gray-600">Regístrate y lleva el cuidado de tu auto al siguiente nivel.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Nombre completo" />
            <x-text-input id="name" class="mt-2 block w-full rounded-xl bg-white px-3.5 py-3 text-sm" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Tu nombre" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl bg-white px-3.5 py-3 text-sm" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nombre@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="mt-2 block w-full rounded-xl bg-white px-3.5 py-3 text-sm" type="password" name="password" required autocomplete="new-password" placeholder="Crea una contraseña" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirma tu contraseña" />
            <x-text-input id="password_confirmation" class="mt-2 block w-full rounded-xl bg-white px-3.5 py-3 text-sm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repite tu contraseña" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Consentimiento: obligatorio; el servidor guarda la fecha en users.privacy_accepted_at. --}}
        <div>
            <label for="privacy" class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3.5 text-sm leading-snug text-gray-700">
                <input id="privacy" type="checkbox" name="privacy" value="1" required @checked(old('privacy'))
                    class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-[#bd7710] focus:ring-[#f5a623]">
                <span>
                    He leído y acepto los
                    <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener" class="font-bold text-[#bd7710] underline underline-offset-2 transition hover:text-[#f5a623]">términos de servicio</a>
                    y el
                    <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="font-bold text-[#bd7710] underline underline-offset-2 transition hover:text-[#f5a623]">aviso de privacidad</a>,
                    incluido el tratamiento de mis datos para crear mi cuenta y atender mis pedidos.
                </span>
            </label>
            <x-input-error :messages="$errors->get('privacy')" class="mt-2" />
        </div>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#f5a623] px-4 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-[#f5a623] focus:ring-offset-2">
            Crear cuenta IFIX
            <span class="material-symbols-outlined text-lg" aria-hidden="true">arrow_forward</span>
        </button>
    </form>

    <p class="mt-6 border-t border-gray-200 pt-5 text-center text-sm text-gray-600">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="ml-1 font-bold text-[#bd7710] transition hover:text-[#f5a623]">Inicia sesión</a>
    </p>
</x-guest-layout>
