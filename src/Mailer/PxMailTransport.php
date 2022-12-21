<?php

namespace mindtwo\LaravePxMail\Mailer;

use mindtwo\LaravePxMail\Client\ApiClient;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PxMailTransport extends AbstractTransport
{
    protected ApiClient $client;

    /**
     * Create PxMailTransport instance
     *
     * @param ApiClient $client
     */
    public function __construct(
        array $config
    ) {
        $this->validateConfig($config);

        $this->client = new ApiClient($config);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $from = $email->getFrom()[0]->getAddress();

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

    private function validateConfig(array $config)
    {
        if (!isset($config['tenant'])) {
            throw new \Exception("Could not load px mail driver. Config value for 'px-mail.tenant' is missing...", 1);
        }

        if (!isset($config['client_id'])) {
            throw new \Exception("Could not load px mail driver. Config value for 'px-mail.client_id' is missing...", 1);
        }

        if (!isset($config['client_secret'])) {
            throw new \Exception("Could not load px mail driver. Config value for 'px-mail.client_secret' is missing...", 1);
        }
    }
}
