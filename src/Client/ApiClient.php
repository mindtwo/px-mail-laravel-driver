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
     * The stage the app runs in.
     *
     * @var string
     */
    private string $stage;

    /**
     * The url for the mailer.
     *
     * @var string
     */
    private string $mailerUrl;

    /**
     * Your tx mail tenant to send mails from.
     *
     * @var string
     */
    private string $tenant;

    /**
     * Your tx mail client id.
     *
     * @var string
     */
    private string $clientId;

    /**
     * Your tx mail client secret.
     *
     * @var string
     */
    private string $clientSecret;

    /**
     * Create a new client instance.
     *
     * @param string $stage - The stage the app runs in
     * @param string $mailerUrl - The url for the mailer
     * @param string $tenant - Your tx mail tenant to send mails from
     * @param string $clientId - Your tx mail client id
     * @param string $clientSecret - Your tx mail client secret
     */
    public function __construct(
        string $stage,
        string $mailerUrl,
        string $tenant,
        string $clientId,
        string $clientSecret,
    ) {
        $this->stage = $stage;
        $this->mailerUrl = $mailerUrl;
        $this->tenant = $tenant;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
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
            foreach ($email->getTo() as $address) {
                $response = $this->send($address, $from, $email);
            }
        } catch (Throwable $e) {

            Log::error('Failed to send mail', [
                'tenant' => $this->tenant,
                'client_id' => $this->clientId,
                'stage' => $this->stage,
                'url' => $this->mailerUrl,
                'sender' => is_string($from) ? $from : $from->getAddress(),
                'message' => $e->getMessage(),
            ]);

            return false;
        }

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
        return Http::baseUrl(
            $this->mailerUrl
        )
            ->withHeaders(
                $this->headers()
            )
            ->post("/{$this->tenant}/sendMail", array_filter([
                'sender' => is_string($from) ? $from : $from->getAddress(),
                'senderName' => is_string($from) ? null : $from->getName(),
                'recipient' => is_string($to) ? $to : $to->getAddress(),
                'subject' => $email->getSubject(),
                'body' => $email->getHtmlBody() ?? 'no body',
                'attachments' => collect($email->getAttachments())->map(fn (DataPart $attachment) => [
                    'filename' => $attachment->getFilename() ?? 'file.pdf',
                    'file' => $attachment->bodyToString(),
                ])->toArray(),
            ]))
            ->throw();
    }

    /**
     * Get the headers for the request.
     *
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-m2m-authorization' => sprintf('%s:%s', $this->clientId, urlencode($this->clientSecret)),
        ];
    }
}
