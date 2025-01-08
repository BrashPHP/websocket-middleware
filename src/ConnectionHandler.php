<?php

declare(strict_types=1);

namespace Brash\WebSocketMiddleware;

use Brash\Websocket\Compression\ServerCompressionContext;
use Brash\Websocket\Config\Config;
use Brash\Websocket\Connection\Connection;
use Brash\Websocket\Connection\ConnectionFactory as BrashConnFactory;
use Brash\Websocket\Events\OnNewConnectionOpenEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\Stream\CompositeStream;
use React\Stream\ThroughStream;

class ConnectionHandler
{
    public function __construct(
        private readonly BrashConnFactory $connectionFactory,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Config $config
    ) {}

    public function handleNewConnection(
        ServerRequestInterface $request,
        ThroughStream $inStream,
        ThroughStream $outStream,
        ?ServerCompressionContext $compression
    ): Connection {
        $connection = $this->connectionFactory->createConnection(
            new CompositeStream($inStream, $outStream),
            $this->logger,
            $this->eventDispatcher,
            $this->config,
            $request->getServerParams()['REMOTE_ADDR'] ?? ''
        );

        $connection->completeHandshake();
        $connection->setCompression($compression);

        $connection->getEventDispatcher()->dispatch(new OnNewConnectionOpenEvent($connection));

        return $connection;
    }
}
