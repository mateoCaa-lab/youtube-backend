<?php

return [
    // 1. Asegúrate de incluir 'auth/*' porque ahí movimos las rutas de Google
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'auth/*', 'login'],

    'allowed_methods' => ['*'],

    // 2. Agrega la URL de Vercel (sin la barra '/' al final)
    'allowed_origins' => [
        'http://localhost:5173',
        'https://youtube-frontend-khaki.vercel.app'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // 3. Importante mantener esto en true para las cookies/tokens
    'supports_credentials' => true, 
];
?>