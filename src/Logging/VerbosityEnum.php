<?php

namespace mindtwo\LaravelPxMail\Logging;

enum VerbosityEnum: string
{
    case quiet = 'quiet';
    case error = 'error';
    case verbose = 'verbose';
    case debug = 'debug';

    public function getLevel(): int
    {
        return match ($this) {
            self::quiet => 0,
            self::error => 1,
            self::verbose => 2,
            self::debug => 3,
        };
    }

    public static function outputsLog(?VerbosityEnum $verbosity = null, ?VerbosityEnum $configVerbosity = null): bool
    {
        // If no verbosity is given, we assume the default verbosity
        if (null === $verbosity || $verbosity === self::quiet) {
            return false;
        }

        $configVerbosity = self::from(config('px-mail.verbosity', 'quiet'));

        return $configVerbosity->getLevel() >= $verbosity->getLevel();
    }
}
