<?php

namespace App\Data;

final readonly class AddressData
{
    public function __construct(
        public int $id,
        public ?string $title,
        public string $firstName,
        public string $lastName,
        public ?string $companyName,
        public string $lineOne,
        public ?string $lineTwo,
        public string $city,
        public ?string $state,
        public ?string $postcode,
        public int $countryId,
        public string $countryName,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public bool $shippingDefault,
        public bool $billingDefault,
    ) {}
}
