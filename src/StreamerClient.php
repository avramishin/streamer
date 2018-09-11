<?php
namespace App;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Exception;

class StreamerClient
{
    /**
     * @var ConnectionInterface
     */
    var $connection;
    var $subscriptions = [];

    function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    function getId()
    {
        return (int)$this->connection->resourceId;
    }

    function isSubscribed($destination)
    {
        return isset($this->subscriptions[$destination]);
    }

    function subscribe($destination)
    {
        $this->subscriptions[$destination] = $destination;
    }

    function unsubscribe($destination)
    {
        if (isset($this->subscriptions[$destination])) {
            unset($this->subscriptions[$destination]);
        }
    }

    function send($frame)
    {
        return $this->connection->send(json_encode($frame));
    }

    function close(){
        return $this->connection->close();
    }
}