<?php

declare(strict_types=1);

namespace Brash\WebSocketMiddleware;

use Brash\Websocket\Connection\ConnectionHandshake;
use Brash\Websocket\Exceptions\WebSocketException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

final readonly class HttpHandler
{
    public function __construct(
        private ConnectionHandshake $handshaker,
        private array $paths
    ) {}

    public function handle(ServerRequestInterface $request, ?callable $next = null): ResponseInterface
    {
        if ($this->isPathInvalid($request)) {
            return $next ? $next($request) : new Response(404);
        }

        $response = $this->handshaker->handshake($request);

        if ($response instanceof WebSocketException) {
            return new Response(400, body: $response->getMessage());
        }

        return $response;
    }

    private function isPathInvalid(ServerRequestInterface $request): bool
    {
        return $this->paths !== [] && ! in_array($request->getUri()->getPath(), $this->paths, true);
    }
}
