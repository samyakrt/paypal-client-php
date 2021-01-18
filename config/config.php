<?php

return [
    'client_id' => env('CLIENT_ID'),
    'client_secret' => env('CLIENT_SECRET'),
    'scopes' => env('SCOPES','openid profile email address https://uri.paypal.com/services/invoicing'),
    'mode' => env('MODE','sandbox'),
    'auth_redirect_uri' => env('AUTH_REDIRECT_URI')
];