<?php

declare (strict_types= 1);

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines all the feature toggles for your application.
    | You can enable or disable features globally or for specific users.
    |
    */

    // Global toggle to allow role changing
    'allow_role_self_assignment' => env('ALLOW_ROLE_SELF_ASSIGNMENT', false)
];