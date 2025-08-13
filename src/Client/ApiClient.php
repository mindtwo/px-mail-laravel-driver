<?php

namespace mindtwo\LaravelPxMail\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
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
     * MailerApiVersion
     *
     * @var string
     */
    private string $mailerApiVersion;

    /**
     * Debug mode.
     *
     * @var bool
     */
    private bool $debug;

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
        string $mailerApiVersion = 'v1',
        bool $debug = false,
    ) {
        $this->stage = $stage;
        $this->mailerUrl = rtrim($mailerUrl, '/');
        $this->tenant = $tenant;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->mailerApiVersion = $mailerApiVersion;

        $this->debug = $debug;
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
        // Get the sender and recipient addresses
        $sender = is_string($from) ? $from : $from->getAddress();
        $recipient = is_string($to) ? $to : $to->getAddress();

        if (empty($sender) || empty($recipient)) {
            throw new RuntimeException('Sender and recipient cannot be empty.');
        }

        $mailJson = array_filter([
            'sender' => $sender,
            'senderName' => is_string($from) ? null : $from->getName(),
            'recipient' => is_string($to) ? $to : $to->getAddress(),
            'subject' => $email->getSubject(),
            'body' => $email->getHtmlBody() ?? 'no body',
            'attachments' => collect($email->getAttachments())->map(fn (DataPart $attachment) => [
                'filename' => $attachment->getFilename() ?? 'file.pdf',
                'file' => $attachment->bodyToString(),
            ])->toArray(),
        ]);

        $baseUrl = $this->getBaseUrl();

        if ($this->debug) {
            // Log the mail sending details
            Log::info('Sending mail', [
                'tenant' => $this->tenant,
                'client_id' => $this->clientId,
                'url' => $baseUrl,
                'sender' => $sender,
                'recipient' => $this->getAnonymizedEmail($to),
            ]);
        }

        // Send the mail via HTTP POST request
        return Http::baseUrl(
            $baseUrl
        )
            ->withHeaders(
                $this->headers()
            )
            ->post("/{$this->tenant}/sendMail", $mailJson)
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

    /**
     * Get the base URL for the API.
     *
     * @return string
     */
    private function getBaseUrl(): string
    {
        return sprintf('%s/%s', rtrim($this->mailerUrl, '/'), $this->mailerApiVersion);
    }

    /**
     * Anonymize the email address by replacing the local part with asterisks.
     *
     * @param Address|string $email
     * @return string
     * @throws RuntimeException
     */
    private function getAnonymizedEmail(Address|string $email): string
    {
        if (! is_string($email)) {
            $email = $email->getAddress();
        }

        $split = explode('@', $email);
        if (count($split) !== 2) {
            throw new RuntimeException('Invalid email address format.');
        }

        // anonymize the local part
        // Keep the first letter and replace the rest with asterisks
        $localPart = $split[0];
        $localPart = $localPart[0] . str_repeat('*', max(0, strlen($localPart) - 1));

        return sprintf('%s@%s', $localPart, $split[1]);
    }
}
