<?php

require __DIR__ . '/../vendor/autoload.php';

use Stomp\Client;
use Stomp\Network\Connection;
use Stomp\StatefulStomp;
use Stomp\Transport\Message;

class MyCustomMessage extends Message
{
    private $user;

    private $time;

    public function __construct($user, DateTime $time)
    {
        $this->user = $user;
        $this->time = $time;
        parent::__construct(
            $this->generateBody(),
            ['content-type' => 'text/MyCustomMessage'] 
        );
    }


    public function getUser()
    {
        return $this->user;
    }

    public function getTime()
    {
        return $this->time;
    }

    private function generateBody()
    {
        return $this->user . '|' . $this->time->getTimestamp() . '|' . $this->time->getTimezone()->getName();
    }

    public function __toString()
    {
        $this->body = $this->generateBody();
        return parent::__toString();
    }
}

// setup a connection
$connection = new Connection('tcp://173.212.195.56:61613');

$connection->getParser()->getFactory()->registerResolver(
    function ($command, array $headers, $body) {
        
        if ($command === 'MESSAGE' && isset($headers['content-type']) && $headers['content-type'] == 'text/MyCustomMessage') {
            if (preg_match('/^(.+)\|(\d+)\|(.+)$/', $body, $matches)) {
                $date = DateTime::createFromFormat('U', intval($matches[2]), new DateTimeZone($matches[3]));
                $date->setTimezone(new DateTimeZone($matches[3]));
                $user = $matches[1];
                return new MyCustomMessage($user, $date);
            }
        }
        
        return null;
    }
);

$stomp = new StatefulStomp(new Client($connection));
$stomp->subscribe('/queue/examples');

$stomp->send(
    '/queue/examples',
    new MyCustomMessage('Mubeen Expertflow', new DateTime('yesterday 18:00', new DateTimeZone('Asia/Karachi')))
);

$message = $stomp->read();

/** @var $message MyCustomMessage */
echo get_class($message), PHP_EOL;
echo sprintf('Message from %s (%s)', $message->getUser(), $message->getTime()->format('Y-m-d H:i:s e'));

$stomp->unsubscribe();



