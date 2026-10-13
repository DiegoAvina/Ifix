<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Session;

class PostalCodeService
{
    private const SESSION_KEY = 'shipping.postal_code';

    public function get(): ?string
    {
        return Session::get(self::SESSION_KEY);
    }

    public function set(string $postalCode): void
    {
        Session::put(self::SESSION_KEY, $postalCode);
    }

    public function has(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public function forget(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}