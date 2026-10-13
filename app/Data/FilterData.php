<?php

namespace App\Data;

final readonly class FilterData
{
    /**
     * @param FilterOptionData[] $options
     */
    public function __construct(

        public string $key,

        public string $title,

        public array $options = [],

        public mixed $extra = null,

    ) {
    }
}