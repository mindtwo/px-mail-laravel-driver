<?php declare(strict_types=1);

namespace mindtwo\LaravelPxMail\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use mindtwo\LaravelPxMail\Contracts\ProvidesRecipientId;
use mindtwo\TwoTility\Http\BaseApiClient;
use RuntimeException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Throwable;

class ApiClient extends BaseApiClient
{
    /** The url for the mailer. */
    private string $mailerUrl;

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
        private string $stage,
        string $mailerUrl,
        private string $tenant,
        private string $clientId,
        private string $clientSecret,
        /** MailerApiVersion. */
        private string $mailerApiVersion = 'v1',
    ) {
        $this->mailerUrl = mb_rtrim($mailerUrl, '/');
    }

    public function apiName(): string
    {
        return 'px-mail';
    }

    /**
     * {@inheritDoc}
     */
    public function baseUrl(): string
    {
        return sprintf('%s/%s', $this->mailerUrl, $this->mailerApiVersion);
    }

    /**
     * Send mail with body.
     *
     * @return bool
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

    protected function configBaseKey(): string
    {
        return 'px-mail';
    }

    /**
     * {@inheritDoc}
     */
    protected function afterConfigure(PendingRequest $client): void
    {
        $contextHeaders = array_filter([
            'x-context-tenant-code' => $this->config('context.tenant'),
            'x-context-domain-code' => $this->config('context.domain'),
        ]);

        if (! empty($contextHeaders)) {
            $client->withHeaders($contextHeaders);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function headers(): array
    {
        return array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-m2m-authorization' => sprintf('%s:%s', $this->clientId, urlencode($this->clientSecret)),
        ], parent::headers());
    }

    /**
     * Send mail to.
     *
     * @return Response
     */
    private function send(Address|string $to, Address|string $from, Email $email)
    {
        // Get the sender and recipient addresses
        $sender = is_string($from) ? $from : $from->getAddress();
        $recipient = is_string($to) ? $to : $to->getAddress();

        if (empty($sender) || empty($recipient)) {
            throw new RuntimeException('Sender and recipient cannot be empty.');
        }

        $this->debugLog($sender, $to);

        $mailJson = array_filter([
            'sender' => $sender,
            'senderName' => is_string($from) ? null : $from->getName(),
            'recipient' => $recipient,
            'subject' => $email->getSubject(),
            'body' => $email->getHtmlBody() ?? 'no body',
            'attachments' => collect($email->getAttachments())->map(fn (DataPart $attachment) => [
                'filename' => $attachment->getFilename() ?? 'file.pdf',
                'file' => $attachment->bodyToString(),
            ])->toArray(),
            'userId' => $email instanceof ProvidesRecipientId ? $email->getRecipientUserId() : null,
        ]);

        return $this->client()->post("/{$this->tenant}/sendMail", $mailJson);
    }

    private function debugLog(string $sender, Address|string $to): void
    {
        if (! $this->config('debug', false)) {
            return;
        }

        try {
            Log::info(sprintf('[%s] Sending mail', $this->apiName()), [
                'tenant' => $this->tenant,
                'client_id' => $this->clientId,
                'url' => $this->baseUrl(),
                'sender' => $sender,
                'recipient' => $this->getAnonymizedEmail($to),
            ]);
        } catch (Throwable) {
            Log::error(sprintf('[%s] Failed to log mail sending details', $this->apiName()));
        }
    }

    /**
     * Anonymize the email address by replacing the local part with asterisks.
     *
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
        $localPart = $localPart[0].str_repeat('*', max(0, mb_strlen($localPart) - 1));

        return sprintf('%s@%s', $localPart, $split[1]);
    }
}
