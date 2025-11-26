<?php

namespace mindtwo\LaravelPxMail\Contracts;

interface ProvidesRecipientId
{

    /**
     * Get the recipients unique identifier.
     *
     * @return null|string
     */
    public function getRecipientUserId(): ?string;

}
