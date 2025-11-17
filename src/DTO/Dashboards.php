<?php

namespace InterWorks\PowerBI\DTO;

use Illuminate\Support\Collection;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Traits\Responses\HasResponse;

class Dashboards implements WithResponse
{
    use HasResponse;

    /**
     * Constructor
     *
     * @param  Collection<int, Dashboard>  $dashboards
     */
    public function __construct(
        public readonly Collection $dashboards,
    ) {}

    /**
     * Create a DashboardsCollection from an array
     *
     * @param  array<int, array{
     *    id: string,
     *    isReadOnly: bool,
     *    isOnDedicatedCapacity: bool,
     *    type: string,
     *    name: string
     * }> $data The array to parse.
     */
    public static function fromArray(array $data): self
    {
        $dashboards = collect($data)->map(function ($item) {
            return Dashboard::fromItem($item);
        });

        return new self($dashboards);
    }
}
