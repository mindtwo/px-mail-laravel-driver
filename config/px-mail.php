<?php declare(strict_types=1);

return [
    /*
     * The stage the app runs in
     *
     * Default: env('APP_ENV')
     */
    'stage' => env('APP_ENV') === 'local' ? 'preprod' : env('APP_ENV'),

    /*
     * The url for the mailer
     *
     * Default: env('TX_MAIL_URL', 'https://tx-mail.api.pl-x.cloud/v1/')
     */
    'mailer_url' => env('TX_MAIL_URL'),

    /*
     * The version of the mailer API to use
     * Default: env('TX_MAIL_VERSION', 'v1')
     *
     * Note: This is used to determine the API endpoint URL.
     */
    'mailer_api_version' => env('TX_MAIL_API_VERSION', 'v1'),

    /*
     * Enable HTTP-level debug logging (requests, responses, errors).
     *
     * Default: env('TX_MAIL_DEBUG', false)
     */
    'debug' => env('TX_MAIL_DEBUG', false),

    /*
     * Log outgoing send attempts (sender, recipient, tenant, url).
     * Can be enabled independently of debug mode.
     *
     * Default: env('TX_MAIL_LOG_SEND', false)
     */
    'log_send' => env('TX_MAIL_LOG_SEND', false),

    /*
     * Your tx mail tenant to send mails from
     *
     * Default: env('TX_MAIL_TENANT')
     */
    'tenant' => env('TX_MAIL_TENANT'),

    /*
     * Your tx mail client id
     *
     * Default: env('TX_MAIL_CLIENT_ID')
     */
    'client_id' => env('TX_MAIL_CLIENT_ID'),

    /*
     * Your tx mail client secret
     *
     * Default: env('TX_MAIL_CLIENT_SECRET')
     */
    'client_secret' => env('TX_MAIL_CLIENT_SECRET'),
];
