<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 19:21
 */

namespace Devristo\TorrentTracker\TcpServer;


use Devristo\TorrentTracker\Configuration;
use Devristo\TorrentTracker\Exceptions\TrackerException;
use Devristo\TorrentTracker\HttpServer\Serializer;
use Devristo\TorrentTracker\Message\AnnounceRequestInterface;
use Devristo\TorrentTracker\Message\TrackerResponse;
use Devristo\TorrentTracker\Message\ErrorResponse;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\Tracker;
use Evenement\EventEmitter;
use Guzzle\Http\Message\Request;
use Psr\Log\InvalidArgumentException;
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

    public function __construct(Tracker $tracker, LoggerInterface $logger, Configuration $configuration){
        $this->logger = $logger;
        $this->conversion = new Serializer($configuration);
        $this->tracker = $tracker;
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
            $request = $this->conversion->parseRequest($request);
            $request->setRequestEndpoint($endpoint);

            if($request instanceof AnnounceRequestInterface)
                return $this->tracker->announce($request);
            else throw new InvalidArgumentException();

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