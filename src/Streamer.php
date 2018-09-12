<?php
namespace App;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Exception;

class Streamer implements MessageComponentInterface
{
    /**
     * @var StreamerClient[]
     */
    protected $clients;

    function __construct()
    {
        $this->clients = [];
    }

    function actionSubscribe($msg, $clientId)
    {
        if (empty($msg->destination)) {
            throw new Exception("Missing 'destination' to subscribe");
        }

        $me = $this->clients[$clientId];
        $me->subscribe($msg->destination);
    }

    function actionUnsubscribe($msg, $clientId)
    {
        if (empty($msg->destination)) {
            throw new Exception("Missing 'destination' to unsubscribe");
        }

        $me = $this->clients[$clientId];
        $me->unsubscribe($msg->destination);
    }

    function actionPublish($msg, $clientId)
    {
        if (empty($msg->destination)) {
            throw new Exception("Missing 'destination' to publish to");
        }

        if (!isset($msg->payload)) {
            throw new Exception("Missing 'payload' to publish");
        }

        foreach ($this->clients as $id => $client) {
            if ($client->isSubscribed($msg->destination)) {
                $client->send([
                    "type" => "update",
                    "source" => $msg->destination,
                    "payload" => $msg->payload
                ]);
            }
        }
    }

    function onOpen(ConnectionInterface $connection)
    {
        $client = new StreamerClient($connection);
        $this->clients[$client->getId()] = $client;
        $this->log("New connection! ({$client->getId()})");
    }

    function onMessage(ConnectionInterface $fromConn, $msg)
    {
        $client = new StreamerClient($fromConn);
        $msg = @json_decode($msg);

        if (!isset($msg->type)) {
            return $client->send([
                "source" => "general",
                "type" => "error",
                "message" => "message type missing"
            ]);
        }

        $method = "action" . ucfirst($msg->type);
        if (!method_exists($this, $method)) {
            return $client->send([
                "source" => "general",
                "type" => "error",
                "message" => "unknown message type [{$msg->type}]"
            ]);
        }

        try {
            $this->{$method}($msg, $client->getId());
        } catch (Exception $e) {
            return $client->send([
                "source" => "general",
                "type" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    function onClose(ConnectionInterface $conn)
    {
        $clientId = (int)$conn->resourceId;
        unset($this->clients[$clientId]);
        $this->log("Conn. {$clientId} has disconnected");
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->log("An error has occurred: {$e->getMessage()}");
        $clientId = (int)$conn->resourceId;
        $conn->close();
        unset($this->clients[$clientId]);
    }

    function log($msg)
    {
        if (is_array($msg) || is_object($msg)) {
            $msg = print_r($msg, true);
        }

        echo "[" . date('Y-m-d H:i:s') . "]: {$msg}\n";
    }
}