<?php

use Illuminate\Support\Collection;
use InterWorks\PowerBI\Connectors\PowerBIServicePrincipal;
use InterWorks\PowerBI\DTO\ArtifactAccessEntry;
use InterWorks\PowerBI\DTO\ArtifactAccessResponse;
use InterWorks\PowerBI\DTO\User;
use InterWorks\PowerBI\Enums\ArtifactType;
use InterWorks\PowerBI\Requests\Admin\Users\GetUserArtifactAccessAsAdmin;
use InterWorks\PowerBI\Tests\Fixtures\PowerBIFixture;
use Saloon\Http\Faking\MockClient;

test('can get user artifact access as admin', function () {
    $mockClient = new MockClient([
        GetUserArtifactAccessAsAdmin::class => new PowerBIFixture('admin/users/get-user-artifact-access-as-admin'),
    ]);

    $powerBIConnection = new PowerBIServicePrincipal(
        env('POWER_BI_TENANT'),
        env('POWER_BI_CLIENT_ID'),
        env('POWER_BI_CLIENT_SECRET')
    );
    $authenticator = $powerBIConnection->getAccessToken();
    $powerBIConnection->authenticate($authenticator);
    $request = new GetUserArtifactAccessAsAdmin(userId: 'test-user@example.com');
    $response = $powerBIConnection->send($request, mockClient: $mockClient);

    expect($response->status())->toBe(200);
    expect($response->dto())->toBeInstanceOf(ArtifactAccessResponse::class);
});

test('response includes continuation token when available', function () {
    $mockClient = new MockClient([
        GetUserArtifactAccessAsAdmin::class => new PowerBIFixture('admin/users/get-user-artifact-access-as-admin'),
    ]);

    $powerBIConnection = new PowerBIServicePrincipal(
        env('POWER_BI_TENANT'),
        env('POWER_BI_CLIENT_ID'),
        env('POWER_BI_CLIENT_SECRET')
    );
    $authenticator = $powerBIConnection->getAccessToken();
    $powerBIConnection->authenticate($authenticator);
    $request = new GetUserArtifactAccessAsAdmin(userId: 'test-user@example.com');
    $response = $powerBIConnection->send($request, mockClient: $mockClient);

    $dto = $response->dto();
    expect($dto->continuationToken)->toBeString();
    expect($dto->continuationToken)->toBe('next-page-token-example');
    expect($dto->continuationUri)->toBeString();
    expect($dto->continuationUri)->toContain('continuationToken=next-page-token-example');
});

test('artifact access entries have correct structure', function () {
    $mockClient = new MockClient([
        GetUserArtifactAccessAsAdmin::class => new PowerBIFixture('admin/users/get-user-artifact-access-as-admin'),
    ]);

    $powerBIConnection = new PowerBIServicePrincipal(
        env('POWER_BI_TENANT'),
        env('POWER_BI_CLIENT_ID'),
        env('POWER_BI_CLIENT_SECRET')
    );
    $authenticator = $powerBIConnection->getAccessToken();
    $powerBIConnection->authenticate($authenticator);
    $request = new GetUserArtifactAccessAsAdmin(userId: 'test-user@example.com');
    $response = $powerBIConnection->send($request, mockClient: $mockClient);

    $dto = $response->dto();
    expect($dto->artifactAccessEntities)->toBeInstanceOf(Collection::class);
    expect($dto->artifactAccessEntities)->toHaveCount(5);

    foreach ($dto->artifactAccessEntities as $entry) {
        expect($entry)->toBeInstanceOf(ArtifactAccessEntry::class);
        expect($entry->artifactId)->toBeString();
        expect($entry->displayName)->toBeString();
        expect($entry->artifactType)->toBeInstanceOf(ArtifactType::class);
        expect($entry->accessRight)->toBeString();
    }
});

test('artifact access entries include sharer when available', function () {
    $mockClient = new MockClient([
        GetUserArtifactAccessAsAdmin::class => new PowerBIFixture('admin/users/get-user-artifact-access-as-admin'),
    ]);

    $powerBIConnection = new PowerBIServicePrincipal(
        env('POWER_BI_TENANT'),
        env('POWER_BI_CLIENT_ID'),
        env('POWER_BI_CLIENT_SECRET')
    );
    $authenticator = $powerBIConnection->getAccessToken();
    $powerBIConnection->authenticate($authenticator);
    $request = new GetUserArtifactAccessAsAdmin(userId: 'test-user@example.com');
    $response = $powerBIConnection->send($request, mockClient: $mockClient);

    $dto = $response->dto();
    $firstEntry = $dto->artifactAccessEntities->first();

    expect($firstEntry->sharer)->toBeInstanceOf(User::class);
    expect($firstEntry->sharer->emailAddress)->toBe('admin@example.com');
    expect($firstEntry->sharer->displayName)->toBe('Admin User');
    expect($firstEntry->sharer->identifier)->toBeString();
    expect($firstEntry->shareType)->toBeString();
});

test('artifact access entries handle missing sharer field', function () {
    $mockClient = new MockClient([
        GetUserArtifactAccessAsAdmin::class => new PowerBIFixture('admin/users/get-user-artifact-access-as-admin'),
    ]);

    $powerBIConnection = new PowerBIServicePrincipal(
        env('POWER_BI_TENANT'),
        env('POWER_BI_CLIENT_ID'),
        env('POWER_BI_CLIENT_SECRET')
    );
    $authenticator = $powerBIConnection->getAccessToken();
    $powerBIConnection->authenticate($authenticator);
    $request = new GetUserArtifactAccessAsAdmin(userId: 'test-user@example.com');
    $response = $powerBIConnection->send($request, mockClient: $mockClient);

    $dto = $response->dto();
    // Third entry in fixture has no sharer
    $entryWithoutSharer = $dto->artifactAccessEntities->get(2);

    expect($entryWithoutSharer->sharer)->toBeNull();
    expect($entryWithoutSharer->shareType)->toBeNull();
})
