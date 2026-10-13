<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Único mecanismo soportado para conceder acceso al panel /admin.
 *
 * No existe ninguna ruta web ni formulario que pueda otorgar
 * `is_admin = true`; solo un operador con acceso a la consola del
 * servidor puede promover una cuenta, y solo sobre un usuario que ya
 * se haya registrado normalmente por /register.
 */
class PromoteUserToAdmin extends Command
{
    protected $signature = 'admin:promote {email : Email del usuario a promover a administrador}';

    protected $description = 'Concede acceso al panel /admin a un usuario existente';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("No existe ningún usuario con el email \"{$email}\".");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("\"{$user->email}\" ya tiene acceso de administrador.");

            return self::SUCCESS;
        }

        if (! $this->confirm("¿Conceder acceso de administrador a \"{$user->email}\"?")) {
            $this->comment('Cancelado, no se hizo ningún cambio.');

            return self::SUCCESS;
        }

        $user->is_admin = true;
        $user->save();

        $this->info("\"{$user->email}\" ahora es administrador y puede acceder a /admin.");

        return self::SUCCESS;
    }
}
