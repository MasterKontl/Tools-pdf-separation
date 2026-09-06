<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resend API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Resend API key. This key will be used to
    | authenticate with the Resend HTTPS API. Supports both RESEND_KEY
    | and RESEND_API_KEY environment variables.
    |
    */

    'api_key' => env('RESEND_KEY', env('RESEND_API_KEY')),

];
