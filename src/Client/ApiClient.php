<?php

namespace mindtwo\LaravelPxMail\Client;

use Illuminate\Support\Facades\Http;
use mindtwo\LaravelPxMail\Logging\LogVerbose;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Throwable;

class ApiClient
{

    use LogVerbose;

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

    /**
     * Create a new client instance.
     *
     * @param string|null $stage - The stage the app runs in
     * @param string|null $mailerUrl - The url for the mailer
     * @param string|null $tenant - Your tx mail tenant to send mails from
     * @param string|null $clientId - Your tx mail client id
     * @param string|null $clientSecret - Your tx mail client secret
     * @param string $verbosity - The verbosity for the mailer
     */
    public function __construct(
        private ?string $stage = null,
        private ?string $mailerUrl = null,
        private ?string $tenant = null,
        private ?string $clientId = null,
        private ?string $clientSecret = null,
        private string $verbosity = 'quiet',
    ) {
        // create our pxUser macro
        Http::macro('pxMail', function () {
            return Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->baseUrl($this->mailerUrl)
            ->throw();
        });
    }

    /**
     * Send mail with body
     *
     * @param  Address|string  $from
     * @param  Email  $email
     * @return bool
     *
     */
    public function sendMail(Address|string $from, Email $email)
    {
        try {
            $this->log('Sending message to emails', [
                'sender' => is_string($from) ? $from : $from->getAddress(),
                'to' => $email->getTo(),
                'recipient' => collect($email->getTo())->map(fn (Address $address) => $address->getAddress())->join(', '),
                'subject' => $email->getSubject(),
                'stage' => $this->stage,
                'url' => $this->mailerUrl,
            ]);

            foreach ($email->getTo() as $address) {
                $response = $this->send($address, $from, $email);
            }
        } catch (Throwable $e) {
            $this->error('Failed to send message for tenant: ', [
                'sender' => is_string($from) ? $from : $from->getAddress(),
                'tenant' => $this->tenant,
                'client_id' => $this->clientId,
                'stage' => $this->stage,
                'url' => $this->mailerUrl,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        $this->log('Send mail request was successful', [
            'sender' => is_string($from) ? $from : $from->getAddress(),
            'recipient' => collect($email->getTo())->map(fn (Address $address) => $address->getAddress())->join(', '),
            'subject' => $email->getSubject(),
            'stage' => $this->stage,
            'url' => $this->mailerUrl,
        ]);

        return isset($response) && $response->ok();
    }

    /**
     * Send mail to
     *
     * @param Address|string $to
     * @param Address|string $from
     * @param Email $email
     * @return \Illuminate\Http\Client\Response
     */
    private function send(Address|string $to, Address|string $from, Email $email)
    {
        $this->debug('Sending mail', [
            'sender' => is_string($from) ? $from : $from->getAddress(),
            'recipient' => is_string($to) ? $to : $to->getAddress(),
            'subject' => $email->getSubject(),
            'stage' => $this->stage,
            'url' => $this->mailerUrl,
        ]);

        // @phpstan-ignore-next-line
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
}
