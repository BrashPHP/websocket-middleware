<?php

namespace Tests;

use Brash\Websocket\Http\Response;

const URL = "/test";

it('Should not allow undeclared path', function (): void {
    $sut = createSut(["forbidden"]);
    $serverRequest = createRequest();
    $response = $sut->handle($serverRequest);
    expect($response->getStatusCode())->toBe(404);
});

it('Should forward if not a websocket request', function (): void {
    $sut = createSut([URL]);
    $serverRequest = createRequest();
    $response = $sut->handle($serverRequest, fn() => new Response());
    expect($response->getStatusCode())->toBe(200);
});

it('Should validate websocket and return 400 if incorrect request', function (): void {
    $sut = createSut([URL]);
    $serverRequest = createWebsocketRequest();
    $request = $serverRequest->withProtocolVersion('2.0');
    $response = $sut->handle($request);
    expect($response->getStatusCode())->toBe(400);
});

it('Should validate websocket and return 101 if correct request', function (): void {
    $sut = createSut([URL]);
    $serverRequest = createWebsocketRequest();
    $response = $sut->handle($serverRequest);
    expect($response->getStatusCode())->toBe(101);
});


