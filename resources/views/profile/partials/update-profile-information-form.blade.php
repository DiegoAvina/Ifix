<section>
    <header>
        <h2 class="text-lg font-extrabold text-gray-900">
            Datos de tu cuenta
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Actualiza tu nombre y el correo asociado a tu cuenta IFIX.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nombre completo" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full rounded-xl px-3.5 py-3 text-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full rounded-xl px-3.5 py-3 text-sm" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        Tu correo electrónico aún no está verificado.

                        <button form="send-verification" class="font-semibold text-[#bd7710] hover:text-[#f5a623]">
                            Reenviar correo de verificación
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Enviamos un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="rounded-xl bg-[#f5a623] px-5 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">Guardar cambios</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Cambios guardados.</p>
            @endif
        </div>
    </form>
</section>
