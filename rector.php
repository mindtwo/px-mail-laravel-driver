<?php declare(strict_types=1);

use Chiiya\CodeStyle\CodeStyle;
use Rector\Config\RectorConfig;

require __DIR__.'/vendor/autoload.php';

return static function (RectorConfig $config): void {
    $config->paths([
        __DIR__.'/src',
        __DIR__.'/config',
        // __DIR__.'/database',
        __DIR__.'/tests',
    ]);
    $config->skip([
        __DIR__.'/**/*/node_modules',
    ]);
    $config->importNames();
    $config->import(CodeStyle::RECTOR);
};
