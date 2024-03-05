<?php

namespace mindtwo\LaravelPxMail\Mailer;

use mindtwo\LaravelPxMail\Client\ApiClient;
use mindtwo\LaravelPxMail\Logging\LogVerbose;
use mindtwo\LaravelPxMail\Logging\VerbosityEnum;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PxMailTransport extends AbstractTransport
{
    use LogVerbose;

    protected ApiClient $client;

    /**
     * Create PxMailTransport instance
     *
     * @param array $config
     */
    public function __construct(
        array $config
    ) {
        $this->validateConfig($config);

        $this->client = new ApiClient(
            stage: $config['stage'] ?? null,
            mailerUrl: $config['mailer_url'] ?? null,
            tenant: $config['tenant'] ?? null,
            clientId: $config['client_id'] ?? null,
            clientSecret: $config['client_secret'] ?? null,
            verbosity: $config['verbosity'] ?? 'quiet',
        );

        // required to initialize required properties
        parent::__construct(null, null);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $this->debug('Sending mail', [
            'message' => $message->getDebug(),
        ]);

        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $from = $email->getFrom()[0];

        $this->client->sendMail($from, $email);
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

    /**
     * Validate the config
     *
     * @param array $config
     * @return void
     */
    private function validateConfig(array $config)
    {
        if (isset($config['verbosity'])) {
            $test = VerbosityEnum::tryFrom($config['verbosity']);
            if ($test === null) {
                throw new \Exception("Could not load px mail driver. Config value for 'px-mail.verbosity' is invalid...", 1);
            }
        }

        if (!isset($config['mailer_url'])) {
            throw new \Exception("Could not load px mail driver. Config value for 'px-mail.mailer_url' is missing...", 1);
        }

        if (!isset($config['tenant'])) {
            throw new \Exception("Could not load px mail driver. Config value for 'px-mail.tenant' is missing...", 1);
        }

        if (!isset($config['client_id'])) {
            throw new \Exception("Could not load px mail driver. Config value for 'px-mail.client_id' is missing...", 1);
        }

        if (!isset($config['client_secret'])) {
            throw new \Exception("Could not load px mail driver. Config value for 'px-mail.client_secret' is missing...", 1);
        }

        $this->debug('PxMailTransport config is valid', $config);
    }
}
