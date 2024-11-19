<?php

namespace App\Logging\src;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(
                new LineFormatter("[%datetime%] %extra% %channel%.%level_name%: %message% %context%\n", "Y-m-d H:i:s"));
            $handler->pushProcessor(new LogProcessor());
        }
    }
}
