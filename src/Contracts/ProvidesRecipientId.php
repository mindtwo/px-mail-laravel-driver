<?php declare(strict_types=1);

namespace mindtwo\LaravelPxMail\Contracts;

interface ProvidesRecipientId
{
    /**
     * Get the recipients unique identifier.
     */
    public function getRecipientUserId(): ?string;
}
