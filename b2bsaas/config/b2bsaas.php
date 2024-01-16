<?php

// Path: b2bsaas/src/configb2bsaasas.php

return [

    'url_scheme' => env('APP_URL_SCHEME', 'http'),

    'default_team_database_driver' => env('DEFAULT_TEAM_DATABASE_DRIVER', \App\Models\TeamDatabaseType::Mysql->name),

    'features' => [
        'invitation_only' => env('B2BSAAS_INVITATION_ONLY', true),
    ],

    'database_creation_disabled' => env('B2BSAAS_DATABASE_CREATION_DISABLED', false),

    'company' => [
        'empty_logo_path' => 'profile-photos/no_image.jpg',
        'empty_phone' => '(_ _ _) _ _ _- _ _ _ _',
        'empty_fax' => '(_ _ _) _ _ _- _ _ _ _',
        'logo_path' => env('COMPANY_LOGO_PATH'), //resource_path('legacy/qwoffice/print/DigLogo.jpg'
        'name' => env('COMPANY_NAME'),
        'phone' => env('COMPANY_PHONE_NUMBER'),
        'fax' => env('COMPANY_FAX_NUMBER'),
        'street_address' => env('COMPANY_STREET_ADDRESS'),
        'city_state_zip' => env('COMPANY_CITY_STATE_ZIP'),
        'email' => env('COMPANY_EMAIL'),
    ],
];
