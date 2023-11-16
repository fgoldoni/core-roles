<?php

return [
    "defaults" => [
        "guard" => "web",
    ],
    "custom" => [
        "system" => [
            "system.lang",
            "system.profile",
            "system.verification",
            "system.password",
            "system.logout",
        ]
    ],
    "roles" => [
        "administrator" => 'administrator',
        "manager" => 'manager',
        "user" => 'user',
    ],
    'developer_email' => env('DEVELOPER_PASSWORD', "support@sell-first.com"),
    'developer_password' => env('DEVELOPER_PASSWORD', "support@sell-first.com"),
];
