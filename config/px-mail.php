<?php

return [

    /**
     * The stage the app runs in
     *
     * Default: env('APP_ENV')
     */
    'stage' => env('APP_ENV') === 'local' ? 'preprod' : env('APP_ENV'),

    /**
     * The url for the mailer
     *
     * Default: env('TX_MAIL_URL', 'https://tx-mail.api.pl-x.cloud/v1/')
     */
    'mailer_url' => env('TX_MAIL_URL'),

    /**
     * The verbosity for the mailer
     * Valid values: quiet, error, verbose, debug
     *
     * Default: env('TX_MAIL_LOG_LEVEL', 'quiet')
     */
    'verbosity' => env('TX_MAIL_LOG_LEVEL', 'quiet'),

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
