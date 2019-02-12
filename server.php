<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Streamer;
use App\FileMutex;

require_once __DIR__ . "/bootstrap.php";

$port = isset($argv[1]) ? $argv[1] : 8081;
$mutex = new FileMutex("message-queue-streamer-{$port}");
$mutex->lockOrDie();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Streamer()
        )
    ),
    $port
);
$server->run();