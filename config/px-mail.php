<?php

return [

    /**
     * The stage the app runs in
     *
     * Default: env('APP_ENV')
     */
    'stage' => env('APP_ENV') === 'local' ? 'preprod' : env('APP_ENV'),

    /**
     * Your tx mail tenant to send mails from
     *
     * Default: env('TX_MAIL_TENANT')
     */
    'tenant' => env('TX_MAIL_TENANT'),

    /**
     * Your tx mail client id
     *
     * Default: env('TX_MAIL_CLIENT_ID')
     */
    'client_id' => env('TX_MAIL_CLIENT_ID'),

    /**
     * Your tx mail client secret
     *
     * Default: env('TX_MAIL_CLIENT_SECRET')
     */
    'client_secret' => env('TX_MAIL_CLIENT_SECRET'),
];
