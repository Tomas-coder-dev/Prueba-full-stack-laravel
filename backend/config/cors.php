<?php

return [
    'paths' => ['api/*', 'login', 'register', 'logout'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://127.0.0.1:8001'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];