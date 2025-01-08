<?php

use Brash\Websocket\Message\Protocols\AbstractTextMessageHandler;
use Brash\WebSocketMiddleware\MiddlewareFactory;
use React\Http\HttpServer;

require_once __DIR__.'/../vendor/autoload.php';

$factory = new MiddlewareFactory;

$connectionHandlerInterface = new class extends AbstractTextMessageHandler
{
    private array $connections;

    public function __construct()
    {
        $this->connections = [];
    }

    public function onOpen(\Brash\Websocket\Connection\Connection $connection): void
    {
        $this->connections[] = $connection;
    }

    public function handleTextData(string $data, \Brash\Websocket\Connection\Connection $connection): void
    {
        $connection->getLogger()->debug('IP'.':'.$connection->getIp().PHP_EOL);
        $connection->getLogger()->debug('Data: '.$data.PHP_EOL);
        $broadcast = array_filter($this->connections, fn ($conn) => $conn !== $connection);

        foreach ($broadcast as $conn) {
            $conn->writeText($data);
        }
        $connection->writeText($connection->getIp().'says: '.strtoupper($data));
    }
};

$middleware = $factory->create($connectionHandlerInterface);

$socket = new \React\Socket\SocketServer($argv[1] ?? '0.0.0.0:1337');

$server = new HttpServer($middleware);

$server->listen($socket);

echo 'Listening on '.str_replace('tcp:', 'http:', $socket->getAddress()).PHP_EOL;
