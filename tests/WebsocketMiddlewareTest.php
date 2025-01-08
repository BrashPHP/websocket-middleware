<?php

namespace Tests;

use Brash\Websocket\Message\Protocols\ConnectionHandlerInterface;
use Brash\WebSocketMiddleware\MiddlewareFactory;


it(description: 'Should call connection on open once', closure: function () {
    $factory = new MiddlewareFactory();
    /**
     * @var ConnectionHandlerInterface|\Mockery\MockInterface
     */
    $mock = mock(ConnectionHandlerInterface::class);
    $mock->shouldReceive('onOpen')->withAnyArgs()->once();
    $sut = $factory->create($mock);

    $response = $sut->handle(createWebsocketRequest());

    expect($response)->not()->toBeNull();
});
