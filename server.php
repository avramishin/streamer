<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Streamer;
use App\FileMutex;

require_once __DIR__ . "/bootstrap.php";

$mutex = new FileMutex("message-queue-streamer");
$mutex->lockOrDie();

$port = 8081;
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Streamer()
        )
    ),
    $port
);

$server->run();