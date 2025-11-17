<?php

namespace InterWorks\PowerBI\DTO;

use Illuminate\Support\Collection;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Traits\Responses\HasResponse;

class Reports implements WithResponse
{
    use HasResponse;

    /**
     * Constructor
     *
     * @param  Collection<int, Report>  $reports
     */
    public function __construct(
        public readonly Collection $reports,
    ) {}

    /**
     * Create a Reports collection from an array
     *
     * @param  array<int, array{
     *    datasetId: string,
     *    id: string,
     *    name: string,
     *    webUrl: string,
     *    embedUrl: string
     * }> $data The array to parse.
     */
    public static function fromArray(array $data): self
    {
        $reports = collect($data)->map(function ($item) {
            return new Report(
                datasetId: $item['datasetId'],
                id: $item['id'],
                name: $item['name'],
                webUrl: $item['webUrl'],
                embedUrl: $item['embedUrl'],
            );
        });

        return new self($reports);
    }
}
