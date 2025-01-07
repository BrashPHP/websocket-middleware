<?php

declare(strict_types=1);

namespace Brash\WebSocketMiddleware;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    public static function getLogger(): LoggerInterface
    {
        $log = new Logger('cli-ws');
        $log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG)); // <<< uses a stream

        return $log;
    }
}
