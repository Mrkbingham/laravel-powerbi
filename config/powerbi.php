<?php

// config for InterWorks/PowerBI
return [
    /*
    |--------------------------------------------------------------------------
    | Azure AD Tenant ID
    |--------------------------------------------------------------------------
    |
    | The Azure Active Directory tenant ID where your Power BI application
    | is registered. This is used for all authentication flows.
    |
    */
    'tenant' => env('POWER_BI_TENANT', ''),

    /*
    |--------------------------------------------------------------------------
    | Service Principal Credentials (Client Credentials Grant)
    |--------------------------------------------------------------------------
    |
    | Credentials for standard Service Principal authentication. This uses
    | OAuth 2.0 Client Credentials Grant for server-to-server API access
    | without user interaction. Use with PowerBI::forServicePrincipal().
    |
    */
    'client_id' => env('POWER_BI_CLIENT_ID', ''),
    'client_secret' => env('POWER_BI_CLIENT_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Admin Service Principal Credentials (Client Credentials Grant)
    |--------------------------------------------------------------------------
    |
    | Credentials for Admin Service Principal with Power BI administrator
    | rights. Required for accessing admin endpoints (/admin/*).
    | Use with PowerBI::forAdminServicePrincipal().
    |
    */
    'admin_client_id' => env('POWER_BI_ADMIN_CLIENT_ID', ''),
    'admin_client_secret' => env('POWER_BI_ADMIN_CLIENT_SECRET', ''),
];
