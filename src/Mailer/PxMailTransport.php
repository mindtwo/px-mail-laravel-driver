<?php

namespace mindtwo\LaravelPxMail\Mailer;

use mindtwo\LaravelPxMail\Client\ApiClient;
use mindtwo\LaravelPxMail\Exceptions\InvalidConfigException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PxMailTransport extends AbstractTransport
{
    protected ApiClient $client;

    /**
     * Urls for available environments.
     *
     * @var array<string, string>
     */
    private $stageUrls = [
        'testing' => 'https://tx-mail.api.dev.pl-x.cloud/v1/',
        'prod' => 'https://tx-mail.api.pl-x.cloud/v1/',
        'dev' => 'https://tx-mail.api.dev.pl-x.cloud/v1/',
        'preprod' => 'https://tx-mail.api.preprod.pl-x.cloud/v1/',
        'local' => 'https://tx-mail.api.preprod.pl-x.cloud/v1/',
    ];

    public function __construct(
        private ?string $tenant,
        private ?string $clientId,
        private ?string $clientSecret,
        private ?string $stage = null,
        private ?string $mailerUrl = null,
    ) {
        $this->validateConfig();


        [$stage, $mailerUrl] = $this->getMailerStageAndUrl($this->stage, $this->mailerUrl);

        // Update
        $this->stage = $stage;
        $this->mailerUrl = rtrim($mailerUrl, '/');

        $this->client = new ApiClient(
            tenant: $tenant,
            clientId: $clientId,
            clientSecret: $clientSecret,
            stage: $stage,
            mailerUrl: $mailerUrl,
        );

        // required to initialize required properties
        parent::__construct(null, null);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        // @phpstan-ignore-next-line
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $from = $email->getFrom()[0];

        $this->client->sendMail($from, $email);
    }

    /**
     * Get the mailer URL based on the provided stage or URL.
     *
     * @param string|null $stage
     * @param string|null $mailerUrl
     * @return array
     */
    private function getMailerStageAndUrl(?string $stage, ?string $mailerUrl): array
    {
        if ($mailerUrl !== null && $stage !== null) {
            return [$stage, $mailerUrl];
        }

        if ($stage !== null) {
            $mailerUrl = $this->stageUrls[$stage] ?? null;
            if ($mailerUrl === null) {
                throw new InvalidConfigException("Invalid stage provided: {$stage}");
            }
            return [$stage, $mailerUrl];
        }

        return [app()->environment(), $mailerUrl];
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'txmail';
    }

    private function validateConfig(): void
    {
        // Validate required configuration
        if (is_null($this->tenant)) {
            throw new InvalidConfigException('Tenant must be provided.');
        }
        if (is_null($this->clientId)) {
            throw new InvalidConfigException('Client ID must be provided.');
        }
        if (is_null($this->clientSecret)) {
            throw new InvalidConfigException('Client Secret must be provided.');
        }

        if (is_null($this->stage) && is_null($this->mailerUrl)) {
            throw new InvalidConfigException('Either stage or mailerUrl must be provided.');
        }
    }
}
