<?php

use InterWorks\PowerBI\PowerBI;

test('can create PowerBI instance with default config values', function () {
    config()->set('powerbi.tenant', 'test-tenant');
    config()->set('powerbi.client_id', 'test-client-id');
    config()->set('powerbi.client_secret', 'test-client-secret');

    $powerBI = new PowerBI;

    expect($powerBI)->toBeInstanceOf(PowerBI::class);
});

test('can create PowerBI instance with all constructor parameters', function () {
    $powerBI = new PowerBI(
        tenant: 'custom-tenant',
        clientId: 'custom-client-id',
        clientSecret: 'custom-client-secret'
    );

    expect($powerBI)->toBeInstanceOf(PowerBI::class);
});

test('throws exception when only tenant is provided', function () {
    new PowerBI(tenant: 'custom-tenant');
})->throws(InvalidArgumentException::class, 'When overriding credentials, all three parameters (tenant, clientId, clientSecret) must be provided');

test('throws exception when only clientId is provided', function () {
    new PowerBI(clientId: 'custom-client-id');
})->throws(InvalidArgumentException::class, 'When overriding credentials, all three parameters (tenant, clientId, clientSecret) must be provided');
test('throws exception when only clientSecret is provided', function () {
    new PowerBI(clientSecret: 'custom-client-secret');
})->throws(InvalidArgumentException::class, 'When overriding credentials, all three parameters (tenant, clientId, clientSecret) must be provided');

test('throws exception when only tenant and clientId are provided', function () {
    new PowerBI(tenant: 'custom-tenant', clientId: 'custom-client-id');
})->throws(InvalidArgumentException::class, 'When overriding credentials, all three parameters (tenant, clientId, clientSecret) must be provided');
test('throws exception when only tenant and clientSecret are provided', function () {
    new PowerBI(tenant: 'custom-tenant', clientSecret: 'custom-client-secret');
})->throws(InvalidArgumentException::class, 'When overriding credentials, all three parameters (tenant, clientId, clientSecret) must be provided');

test('throws exception when only clientId and clientSecret are provided', function () {
    new PowerBI(clientId: 'custom-client-id', clientSecret: 'custom-client-secret');
})->throws(InvalidArgumentException::class, 'When overriding credentials, all three parameters (tenant, clientId, clientSecret) must be provided');
