<?php

namespace mindtwo\LaravelPxMail\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Email;
use Throwable;

class ApiClient
{
    /**
     * Tx Mail tenant setting
     *
     * @var ?string
     */
    private $tenant = null;

    /**
     * Tx Mail client id
     *
     * @var ?string
     */
    private $clientId = null;

    /**
     * Tx Mail client secret
     *
     * @var ?string
     */
    private $clientSecret = null;

    /**
     * Urls for available environments.
     *
     * @var string[]
     */
    protected $uris = [
        'testing' => 'https://tx-mail.api.dev.pl-x.cloud/v1/',
        'prod' => 'https://tx-mail.api.pl-x.cloud/v1/',
        'dev' => 'https://tx-mail.api.dev.pl-x.cloud/v1/',
        'preprod' => 'https://tx-mail.api.preprod.pl-x.cloud/v1/',
        'local' => 'https://tx-mail.api.preprod.pl-x.cloud/v1/',
    ];

    public function __construct(
        private array $config = [],
    ) {
        $this->stage = $config['stage'] ?? 'prod';

        $this->tenant = $config['tenant'] ?? 'plx';
        $this->clientId = $config['client_id'] ?? null;
        $this->clientSecret = $config['client_secret'] ?? null;

        $uri = $this->getUri();

        // create our pxUser macro
        Http::macro('pxMail', function () use ($uri) {
            return Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->baseUrl($uri)->throw();
        });
    }

    /**
     * Send mail with body
     *
     * @return array|null
     *
     * @throws Throwable
     */
    public function sendMail(string $from, Email $email)
    {
        try {
            foreach ($email->getTo() as $address) {
                $response = $this->send($address->getAddress(), $from, $email);
            }
        } catch (Throwable $e) {
            Log::error('Failed to send message for tenant: ');
            Log::error($this->tenant);
            Log::error($e->getMessage());

            return null;
        }

        // Check if status is 200
        if ($response->status() === 200) {
            return true;
        }

        return false;
    }

    /**
     * Send mail to
     *
     * @param string $to
     * @param Email $email
     * @return void
     */
    private function send(string $to, string $from, Email $email)
    {
        return Http::pxMail()
            ->post("{$this->tenant}/sendMail?client_id={$this->clientId}&client_secret={$this->clientSecret}", [
                'sender' => $from,
                'recipient' => $to,
                'subject' => $email->getSubject(),
                'body' => $email->getHtmlBody(),
            ]);
    }

    /**
     * Get px-user uri
     *
     * @return string
     */
    private function getUri(): string
    {
        return isset($this->stage) ? $this->uris[$this->stage] : $this->uris['prod'];
    }
}
