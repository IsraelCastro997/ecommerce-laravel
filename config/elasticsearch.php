<?php

return [
    'host' => env(
        'ELASTICSEARCH_HOST',
        'https://127.0.0.1:9200'
    ),

    'username' => env('ELASTICSEARCH_USERNAME', 'elastic'),

    'password' => env('ELASTICSEARCH_PASSWORD'),

    'ca_cert' => env('ELASTICSEARCH_CA_CERT'),

    'products_index' => env(
        'ELASTICSEARCH_PRODUCTS_INDEX',
        'ecommerce_products_v1'
    ),
];