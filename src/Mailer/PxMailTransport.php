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
        $this->client = new ApiClient($config);
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $this->client->sendMail($email);
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
}
