<p align="center">
    <img src="https://raw.githubusercontent.com/BrashPHP/websocket-middleware/main/docs/image.png" height="300" alt="WebSocket PHP">
</p>

------

# WebSocket Middleware for react/http

This package provides a PHP React/HTTP WebSocket Middleware.

> **Requires [PHP 8.3+](https://php.net/releases/)**

This library provides a WebSocket message broadcasting solution using the [ReactPHP](https://reactphp.org/) library. It demonstrates how to handle incoming text messages, broadcast messages to other connected clients, and log connection details.

## Requirements

- PHP 8.3 or higher
- Composer
- [ReactPHP](https://reactphp.org/)
- [Brash\Websocket](https://github.com/BrashPHP/websocket) library

## Installation

1. Install the dependencies via Composer:
   ```bash
   composer install
   ```

2. Ensure the autoloader is included:
   ```php
   require_once __DIR__.'/vendor/autoload.php';
   ```

## Usage

### Starting the Server

Run the WebSocket server by executing the script in your terminal:

```bash
php your_script.php [host:port]
```

### Key Components

#### `AbstractTextMessageHandler`
The `AbstractTextMessageHandler` class handles WebSocket text messages. This implementation processes new connections, receives data, and broadcasts it to other connected clients.

#### `MiddlewareFactory`
Creates the middleware required for handling WebSocket connections.

#### `React\Http\HttpServer`
Handles HTTP requests and integrates WebSocket middleware.

### Implementation Details

1. **Connection Handling**
   - The server tracks all active connections in the `$connections` array.
   - When a new connection is established, it is added to the array using the `onOpen` method.

2. **Message Broadcasting**
   - When a client sends a message, it is logged using the connection’s logger.
   - The message is broadcast to all other clients, except the sender.
   - The sender receives a response that includes their IP address and the message content in uppercase.

#### Code Example

Here is the core server implementation:

```php
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
```

### Example Output

When a client connects and sends a message:

1. The server logs the IP address and message data.
2. All other connected clients receive the message.
3. The sender receives a response in uppercase:

```
Client 1 sends: Hello World
Client 2 receives: Hello World
Client 1 receives: 127.0.0.1 says: HELLO WORLD
```

### Config Options

It is optional to set a Config object in the MiddlewareFactory, but you can do it as:

```php

use Brash\Websocket\Config\Config;


$factory = new MiddlewareFactory();
$factory->withConfig(new Config());

```

It is optional to set an array of paths, but this can be achieved as follows:

```php
$factory = new MiddlewareFactory();
$factory->withParams([
    '/test'
]);

```

## License

This library is licensed under the MIT License. See the `LICENSE` file for more details.

## Contributions

Contributions are welcome! Feel free to submit issues or pull requests on GitHub.

## Acknowledgments

- [ReactPHP](https://reactphp.org/)
- [Brash\Websocket](https://github.com/your-project)

---

Enjoy using the WebSocket Message Broadcasting Library!


