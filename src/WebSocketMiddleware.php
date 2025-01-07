<?php

declare(strict_types=1);

namespace Brash\WebSocketMiddleware;

use Brash\Websocket\Compression\CompressionDeflaterDetector;
use Brash\Websocket\Compression\ServerCompressionContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Stream\CompositeStream;
use React\Stream\ThroughStream;

final readonly class WebSocketMiddleware
{
    public function __construct(
        private HttpHandler $httpHandler,
        private ConnectionHandler $connectionHandler
    ) {}

    public function __invoke(ServerRequestInterface $request, ?callable $next = null): ResponseInterface
    {
        return $this->handle($request, $next);
    }

    public function handle(ServerRequestInterface $request, ?callable $next = null): ResponseInterface
    {
        $response = $this->httpHandler->handle($request, $next);

        if ($response->getStatusCode() !== 101) {
            return $next ? $next($request) : $response;
        }

        $compressionDetector = new CompressionDeflaterDetector;
        $compression = $compressionDetector->detect($request);

        if ($compression instanceof ServerCompressionContext) {
            $response = $response->withAddedHeader(
                'Sec-WebSocket-Extensions',
                $compression->compressionConf->getConfAsStringHeader()
            );
        }

        $inStream = new ThroughStream;
        $outStream = new ThroughStream;
        $stream = new CompositeStream($outStream, $inStream);

        $response = new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            $stream
        );

        $connection = $this->connectionHandler->handleNewConnection(
            $request,
            $inStream,
            $outStream,
            $compression
        );

        $inStream->on('data', $connection->onMessage(...));
        $stream->once('end', $connection->onEnd(...));
        $inStream->on('error', $connection->onError(...));

        return $response;
    }
}
