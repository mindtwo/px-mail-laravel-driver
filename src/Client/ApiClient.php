<?php

namespace mindtwo\LaravelPxMail\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Throwable;

class ApiClient
{

    /**
     * Tx Mail stage setting
     *
     * @var ?string
     */
    private $stage = null;

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
     * @param  Address|string  $from
     * @param  Email  $email
     * @return array|null
     *
     */
    public function sendMail(Address|string $from, Email $email)
    {
        try {
            foreach ($email->getTo() as $address) {
                $response = $this->send($address, $from, $email);
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
     * @param Address|string $to
     * @param Address|string $from
     * @param Email $email
     * @return void
     */
    private function send(Address|string $to, Address|string $from, Email $email)
    {
        return Http::pxMail()
            ->post("{$this->tenant}/sendMail?client_id={$this->clientId}&client_secret={$this->clientSecret}", array_filter([
                'sender' => is_string($from) ? $from : $from->getAddress(),
                'senderName' => is_string($from) ? null : $from->getName(),
                'recipient' => is_string($to) ? $to : $to->getAddress(),
                'subject' => $email->getSubject(),
                'body' => $email->getHtmlBody(),
                'attachments' => collect($email->getAttachments())->map(fn (DataPart $attachment) => [
                    'filename' => $attachment->getFilename() ?? 'file.pdf',
                    'file' => $attachment->bodyToString(),
                ])->toArray(),
            ]));
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
