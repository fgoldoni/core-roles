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
    'developer_password' => env('DEVELOPER_PASSWORD', "QTS@2022"),
];
