<?php

return [

    /*
    |--------------------------------------------------------------------------
    | E-Mails Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during sending email for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'verification' => [
        'subject' => '🎮 Verify Email Address',
        'greeting' => 'Dear __:firstname__,',
        'topic' => 'Please click the button below to __verify__ your email address.',
        'button' => 'Verify Email Address',
        'signature' => 'If you did not create an account, no further action is required.',
        'thanks' => 'Thanks,',
    ],

    'reset' => [
        'subject' => '🔐 Reset Password',
        'greeting' => 'Dear __:firstname__,',
        'topic' => 'You are receiving this email because we received a __password reset__ request for your account.',
        'button' => 'Password Reset',
        'signature' => 'This password reset link will expire in :count minutes.',
        'further' => 'If you did not request a password reset, no further action is required.',
        'thanks' => 'Thanks,',
    ],

    'activity' => [
        'subject' => '⚠️ Unusual activity detected',
        'greeting' => 'Dear __:firstname__,',
        'topic' => 'We noticed a __:action__ on your LairGG account:',
        'button' => 'Contact Administrator',
        'signature' => 'If this request was not triggered by you, please immediatly change your password or contact an administrator.',
        'thanks' => 'Thanks,',
    ],
];
