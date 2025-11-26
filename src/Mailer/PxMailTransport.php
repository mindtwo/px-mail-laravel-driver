<?php declare(strict_types=1);

namespace mindtwo\LaravelPxMail\Mailer;

use mindtwo\LaravelPxMail\Client\ApiClient;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PxMailTransport extends AbstractTransport
{
    public function __construct(
        protected ApiClient $client,
    ) {
        // required to initialize required properties
        parent::__construct();
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
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'txmail';
    }
}
