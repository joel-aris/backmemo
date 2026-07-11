<?php

declare(strict_types=1);

return [
    'default' => 'l5-swagger',
    'documentations' => [
        'l5-swagger' => [
            'auth' => [
                'sanctum' => [
                    'type' => 'apiKey',
                    'description' => 'Entrez le token Bearer SANCTUM.',
                    'name' => 'Authorization',
                    'in' => 'header',
                ],
            ],
            'paths' => [
                'annotations' => base_path('app'),
                'docs' => storage_path('api-docs'),
                'views' => base_path('resources/views/vendor/l5-swagger'),
                'base' => env('L5_SWAGGER_BASE_PATH', null),
            ],
            'info' => [
                'title' => env('APP_NAME', 'VALIDIKA API'),
                'description' => 'API sécurisée de gestion documentaire pour l\'Ordre National des Pharmaciens de la RDC.',
                'version' => env('APP_VERSION', '1.0.0'),
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'defaults' => [
                'responses' => [
                    '200' => [
                        'description' => 'Succès',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Non autorisé',
                    ],
                    '403' => [
                        'description' => 'Interdit',
                    ],
                    '404' => [
                        'description' => 'Non trouvé',
                    ],
                    '422' => [
                        'description' => 'Erreur de validation',
                    ],
                    '429' => [
                        'description' => 'Trop de requêtes',
                    ],
                    '500' => [
                        'description' => 'Erreur serveur',
                    ],
                ],
            ],
            'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
            'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
            'proxy' => false,
            'additional_config_url' => null,
            'operations_sort' => 'alpha',
            'validator_url' => null,
        ],
    ],
];
