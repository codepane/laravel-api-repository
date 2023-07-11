<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'db' => [
        'per_page' => 10
    ],
    'api' => [
        'http_codes' => [
            'error' => [
                'unauthorized' => 401,
                'not_found' => 404,
                'validation' => 422,
                'internal_server' => 500,
                'forbidden' => 403
            ],
            'success' => 200
        ]
    ],
];