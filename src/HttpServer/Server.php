<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 19:21
 */

namespace Devristo\TorrentTracker\TcpServer;


use Devristo\TorrentTracker\Exceptions\TrackerException;
use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\TrackerResponse;
use Devristo\TorrentTracker\Message\ErrorResponse;
use Devristo\TorrentTracker\Message\ScrapeRequest;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\ServerInterface;
use Evenement\EventEmitter;
use Guzzle\Http\Message\Request;
use Psr\Log\LoggerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;

class Server extends EventEmitter {
    protected $conversion;
    protected $messageHandlers;
    protected $logger;

    public function __construct(LoggerInterface $logger, array $messageFactory=null){
        $this->logger = $logger;
        $this->conversion = new Serializer($messageFactory);

        $this->messageHandlers = array(
            "announce" => array($this, 'announce'),
            "scrape" => array($this, 'scrape')
        );
    }

    public function bind(LoopInterface $eventLoop, $address="0.0.0.0:6881")
    {
        $endpoint = Endpoint::fromString($address);

        $socket = new \React\Socket\Server($eventLoop);
        $socket->listen($endpoint->getPort(), $endpoint->getIp());

        new IoServer(new HttpServer(new HttpServerListener($this)), $socket, $eventLoop);
        $this->logger->info("TcpServer bound", array(
            "address" => $address
        ));
    }

    public function acceptMessage(Endpoint $endpoint, Request $request) {
        try {
            $request = $this->conversion->decode($request);
            $request->setRequestEndpoint($endpoint);
            $handler = $this->messageHandlers[$request->getMessageType()];
            $promise = call_user_func($handler, $request);
        } catch (\Exception $e){
            $this->logger->error("Cannot handle request", array(
                'exception' => $e
            ));
            $promise = (new Deferred())->reject(new \Exception('Cannot handle request'));
        }

        return $promise;
    }

    public function acceptConnection(ConnectionInterface $connection, Request $request)
    {
        $endpoint = new Endpoint($connection->remoteAddress, 0);

        $this->logger->info("Accepting new HTTP connection", array(
            'address' => $endpoint->toString()
        ));

        $this->acceptMessage($endpoint, $request)->then(
            function(TrackerResponse $trackerResponse) use($connection){
                $response = new \Guzzle\Http\Message\Response(
                    200, array('Content-Type' => 'text/plain'),
                    $this->conversion->encode($trackerResponse)
                );

                $connection->send((string) $response);
                $connection->close();
            }, function($exception) use ($connection, $request){

                if($exception instanceof TrackerException) {
                    $error = new ErrorResponse($exception->getRequest(), $exception->getMessage());
                } else {
                    $error = new ErrorResponse(null, "Unknown error");
                }

                $response = new \Guzzle\Http\Message\Response(
                    200, array('Content-Type' => 'text/plain'),
                    $this->conversion->encode($error)
                );

                $connection->send((string) $response);
                $connection->close();
            }
        );
    }
}