<?php

declare(strict_types=1);

namespace Brash\WebSocketMiddleware;

use Brash\Websocket\Config\Config;
use Brash\Websocket\Connection\ConnectionFactory;
use Brash\Websocket\Connection\ConnectionHandshake;
use Brash\Websocket\Connection\DataHandlerFactory;
use Brash\Websocket\Connection\EventSubscriber;
use Brash\Websocket\Events\Protocols\ListenerProvider;
use Brash\Websocket\Events\Protocols\PromiseEventDispatcher;
use Brash\Websocket\Message\Protocols\ConnectionHandlerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

final class MiddlewareFactory
{
    private ?LoopInterface $loop = null;

    private ?LoggerInterface $logger = null;

    private ?Config $config = null;

    private array $paths = [];

    public function create(ConnectionHandlerInterface $connectionHandler): WebSocketMiddleware
    {
        $listenerProvider = new ListenerProvider;
        $loop = $this->loop ?? Loop::get();
        $config = $this->config ?? new Config(prod: false);
        $logger = $this->logger ?? LoggerFactory::getLogger();
        $subscriber = $this->createEventSubscriber(
            $listenerProvider,
            $config,
            $loop
        );

        $subscriber->onNewConnection($connectionHandler);
        $subscriber->onMessageReceived($connectionHandler);
        $subscriber->onDisconnect($connectionHandler);
        $subscriber->onError($connectionHandler);

        $eventDispatcher = new PromiseEventDispatcher($listenerProvider);
        $httpHandler = new HttpHandler(new ConnectionHandshake, $this->paths);
        $connectionHandler = new ConnectionHandler(
            new ConnectionFactory,
            $logger,
            $eventDispatcher,
            $config
        );

        return new WebSocketMiddleware($httpHandler, $connectionHandler);
    }

    public function withLoop(LoopInterface $loopInterface): static
    {
        $this->loop = $loopInterface;

        return $this;
    }

    public function withLogger(LoggerInterface $loggerInterface): static
    {
        $this->logger = $loggerInterface;

        return $this;
    }

    public function withConfig(Config $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function withPaths(array $paths): static
    {
        $this->paths = $paths;

        return $this;
    }

    private function createEventSubscriber(
        ListenerProviderInterface $listenerProviderInterface,
        Config $config,
        LoopInterface $loop
    ): EventSubscriber {
        return new EventSubscriber(
            $listenerProviderInterface,
            new DataHandlerFactory(
                $config,
                $loop
            )
        );
    }
}
