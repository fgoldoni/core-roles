<?php

return [
    "defaults" => [
        "guard" => "web",
        "permissions" => [
            "system" => [
                "system.lang",
                "system.profile",
                "system.verification",
                "system.password",
                "system.logout",
            ],
            "users" => [
                "users.index",
                "users.api",
                "users.create",
                "users.store",
                "users.show",
                "users.edit",
                "users.update",
                "users.destroy",
                "users.export",
                "users.bulk",
            ],
            "roles" => [
                "roles.index",
                "roles.api",
                "roles.create",
                "roles.store",
                "roles.show",
                "roles.edit",
                "roles.update",
                "roles.destroy",
                "roles.export",
                "roles.bulk",
            ]
        ]
    ],

    "roles" => [
        "administrator" => 'administrator',
        "manager" => 'manager',
        "user" => 'user',
    ],
    'developer_password' => env('DEVELOPER_PASSWORD', "QTS@2022"),
];
