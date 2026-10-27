<?php

use App\Http\Middleware\DocumentationNavigation;

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => ['title' => 'Helios — Documentación del proyecto'],
            'routes' => ['api' => 'documentation'],
            'paths' => [
                'use_absolute_path' => false,
                'swagger_ui_assets_path' => 'vendor/swagger-api/swagger-ui/dist/',
                'docs_json' => 'helios.json',
                'docs_yaml' => 'helios.yaml',
                'format_to_use_for_docs' => 'json',
                'annotations' => [base_path('app/OpenApi')],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => 'documentation/specification',
            'oauth2_callback' => 'documentation/oauth2-callback',
            'middleware' => [
                'api' => [DocumentationNavigation::class],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
            'group_options' => ['middleware' => ['web', 'auth', 'verified']],
        ],
        'paths' => [
            'docs' => storage_path('api-docs'),
            'views' => resource_path('views/vendor/l5-swagger'),
            'base' => null,
            'excludes' => [],
        ],
        'scanOptions' => ['open_api_spec_version' => '3.0.0'],
        'securityDefinitions' => ['securitySchemes' => [], 'security' => []],
        'generate_always' => false,
        'generate_yaml_copy' => true,
        'proxy' => false,
        'additional_config_url' => null,
        'operations_sort' => 'alpha',
        'validator_url' => null,
        'ui' => [
            'display' => ['dark_mode' => false, 'doc_expansion' => 'none', 'filter' => true],
            'authorization' => ['persist_authorization' => false],
        ],
        'constants' => [],
    ],
];
