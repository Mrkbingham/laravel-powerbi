<?php

namespace InterWorks\PowerBI\DTO;

use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Traits\Responses\HasResponse;

class Report implements WithResponse
{
    use HasResponse;

    public function __construct(
        public readonly string $datasetId,
        public readonly string $id,
        public readonly string $name,
        public readonly string $webUrl,
        public readonly string $embedUrl,
    ) {}
}
