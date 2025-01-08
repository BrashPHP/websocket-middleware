<?php

namespace Tests;

use Brash\Websocket\Connection\ConnectionHandshake;
use Brash\Websocket\Http\RequestFactory;
use Brash\WebSocketMiddleware\HttpHandler;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\ServerRequest;

function createSut(array $paths = []): HttpHandler {
    return new HttpHandler(new ConnectionHandshake, $paths );
}

function createRequest(): ServerRequestInterface{
    return new ServerRequest("GET", URL);
}

function createWebsocketRequest(): ServerRequestInterface{
    $requestString = "GET /chat HTTP/1.1\r\nHost: example.com:8000\r\nUpgrade: websocket" .
    "\r\nConnection: Upgrade\r\nSec-WebSocket-Key: any-id==\r\nSec-WebSocket-Version: 13";
    $request = RequestFactory::createRequest($requestString);
    
    return new ServerRequest("GET", URL, $request->getHeaders());
}
