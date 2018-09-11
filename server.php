<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Streamer;

require_once __DIR__ . "/bootstrap.php";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Streamer()
        )
    ),
    8081
);

$server->run();