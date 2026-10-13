<?php

namespace App\Data\Order;

final readonly class OrderAddressData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $companyName,
        public string $lineOne,
        public ?string $lineTwo,
        public string $city,
        public ?string $state,
        public ?string $postcode,
        public string $countryName,
        public ?string $contactEmail,
        public ?string $contactPhone,
    ) {}
}
