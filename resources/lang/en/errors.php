<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exceptions Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during throwed Exceptions for various
    | responses that we need to display in API or Web. You are free to
    | modify these language lines according to your application's requirements.
    |
    */

    'exceptions' => [

        /*
        |--------------------------------------------------------------------------
        | \App\Exceptions\VerifiedUserException Language Lines
        |--------------------------------------------------------------------------
        */

        'verification' => [
            'invalid' => [
                // Messages for users without email-address.
                'message' => 'This account doesn\'t have any email-address.',
                'detail' => 'System can\'t send verification email because the user doesn\'t have any valid email-address.',
                'hint' => 'Invalid email-address. this method is only acceptable for users with valid email-address.',
            ],

            'verified' => [
                // Messages for verified users.
                'message' => 'This account has been verified before.',
                'detail' => 'System can\'t send verification email to verified user.',
                'hint' => 'User verified before. this method is only acceptable for users that didn\'t verify their accounts.',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | \Illuminate\Auth\AuthenticationException Language Lines
        |--------------------------------------------------------------------------
        */

        'authentication' => [
            'detail' => 'Invalid or expired token provided.',
            'hint' => 'Try to refresh token or get a new one.',
        ],

        /*
        |--------------------------------------------------------------------------
        | \Illuminate\Auth\Access\AuthorizationException Language Lines
        |--------------------------------------------------------------------------
        */

        'authorization' => [
            'detail' => 'You dont have permission to do this.',
            'hint' => 'Please make sure the requested model belongs to specific entity or user.',
        ],

        /*
        |--------------------------------------------------------------------------
        | \Illuminate\Database\Eloquent\ModelNotFoundException Language Lines
        |--------------------------------------------------------------------------
        */

        'model' => [
            'detail' => 'no result found for specified model.',
            'hint' => 'Please make sure the requested model is in correct format.',
        ],

        /*
        |--------------------------------------------------------------------------
        | \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException Language Lines
        |--------------------------------------------------------------------------
        */

        'maintenance' => [
            'detail' => 'Application is in maintenance mode.',
            'hint' => 'Please Check available at parameter if it exists in response.',
        ],

        /*
        |--------------------------------------------------------------------------
        | Laravel Passport Package Exceptions Language Lines
        |--------------------------------------------------------------------------
        */

        'passport' => [

            /*
            |--------------------------------------------------------------------------
            | \Laravel\Passport\Exceptions\MissingScopeException Language Lines
            |--------------------------------------------------------------------------
            */

            'scopes' => [
                'detail' => 'The requested scope is invalid, unknown, or malformed.',
                'hint' => '{0} Specify a scope in the request or set a default scope.|{1} Check the :value scope.|[2,*] Check the :value scopes.',
            ],

            /*
            |--------------------------------------------------------------------------
            | \League\OAuth2\Server\Exception\OAuthServerException Language Lines
            |--------------------------------------------------------------------------
            */

            'oauth' => [
                'detail' => 'There is a problem for OAuth Service.',
                'hint' => 'Please contact administrator for more information.',
            ],

            /*
            |--------------------------------------------------------------------------
            | \Illuminate\Auth\AuthenticationException Language Lines
            |--------------------------------------------------------------------------
            */

            'authentication' => [
                'message' => 'You can\'t use first party client tokens for client credentials grant type.',
            ],
        ],

       /*
        |--------------------------------------------------------------------------
        | Laravel Passport Package Exceptions Language Lines
        |--------------------------------------------------------------------------
        */

        'spatie' => [

            /*
            |--------------------------------------------------------------------------
            | \Spatie\Permission\Exceptions\PermissionDoesNotExist Language Lines
            |--------------------------------------------------------------------------
            */

            'permission' => [
                'detail' => 'The requested permission is invalid, unknown, or malformed.',
                'hint' => 'please, Make sure the requested permission exist in database or contact support.',
            ],

            /*
            |--------------------------------------------------------------------------
            | \Spatie\Permission\Exceptions\RoleDoesNotExist Language Lines
            |--------------------------------------------------------------------------
            */

            'role' => [
                'detail' => 'The requested role is invalid, unknown, or malformed.',
                'hint' => 'please, Make sure the requested role exist in database or contact support.',
            ],
        ],
    ],
];
