<?php

namespace InterWorks\PowerBI\Requests\Groups;

use InterWorks\PowerBI\DTO\Groups;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetGroups extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/groups';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        /** @var array{
         * '@odata.context': string,
         * '@odata.count': int,
         * 'value': array<int, array{
         *    id: string,
         *    isReadOnly: bool,
         *    isOnDedicatedCapacity: bool,
         *    type: string,
         *    name: string
         * }>}
         */
        $data = $response->json();

        $groups = Groups::fromArray($data['value']);

        return $groups;
    }
}
