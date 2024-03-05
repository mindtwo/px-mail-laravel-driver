<?php

namespace mindtwo\LaravelPxMail\Logging;

use Illuminate\Support\Facades\Log;

trait LogVerbose {

    /**
     * Logs a message if the verbosity is set to 'verbose' or 'error'
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function log(string $message, array $context = []): void
    {
        $this->logVerbose($message, $context, VerbosityEnum::verbose);
    }

    /**
     * Always logs a message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function debug(string $message, array $context = []): void
    {
        $this->logVerbose($message, $context, VerbosityEnum::debug);
    }

    /**
     * Logs an error message if the verbosity is set to 'error'
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function error(string $message, array $context = []): void
    {
        $this->logVerbose($message, $context, VerbosityEnum::error);
    }

    /**
     * Log a verbose message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logVerbose(string $message, array $context = [], ?VerbosityEnum $verbosity = null): void
    {
        $classVerbosity = $this->getLogVerbosity();
        $verbosity = $verbosity ?? $this->getDefaultVerbosity();

        if (VerbosityEnum::outputsLog($verbosity, $classVerbosity)) {
            Log::error($message, $context);
        }
    }

    /**
     * Get the verbosity
     *
     * @return VerbosityEnum
     */
    protected function getLogVerbosity(): VerbosityEnum
    {
        if (property_exists($this, 'verbosity') && null !== ($verbosity = VerbosityEnum::tryFrom($this->verbosity))) {
            return $verbosity;
        }

        return $this->getDefaultVerbosity();
    }

    private function getDefaultVerbosity(): VerbosityEnum
    {
        return VerbosityEnum::from(config('px-mail.verbosity', 'quiet'));
    }

}
