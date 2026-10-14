<section>
    <header>
        <h2 class="text-lg font-extrabold text-gray-900">
            Cambiar contraseña
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Usa una contraseña segura para mantener protegida tu cuenta.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Contraseña actual" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full rounded-xl px-3.5 py-3 text-sm" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nueva contraseña" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full rounded-xl px-3.5 py-3 text-sm" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nueva contraseña" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full rounded-xl px-3.5 py-3 text-sm" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="rounded-xl bg-[#f5a623] px-5 py-3 text-sm font-extrabold text-neutral-950 transition hover:bg-amber-400">Actualizar contraseña</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Contraseña actualizada.</p>
            @endif
        </div>
    </form>
</section>
