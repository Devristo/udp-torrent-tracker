<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:20
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\UdpServer;

use Datagram\Factory as DatagramFactory;
use Datagram\Socket;
use Devristo\TorrentTracker\Exceptions\TrackerException;
use Devristo\TorrentTracker\Message\TrackerResponse;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\ServerInterface;
use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionResponse;
use Devristo\TorrentTracker\Message\ErrorResponse;
use Devristo\TorrentTracker\Message\ScrapeRequest;
use Evenement\EventEmitter;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use DateTime;
use React\Promise\Deferred;
use React\Promise\DeferredPromise;

class Server extends EventEmitter implements ServerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /** @var  Socket */
    protected $socket;

    /**
     * @var Connection[]
     */
    protected $_connections = array();

    public function __construct(LoggerInterface $logger, array $messageFactory=null){
        $this->logger = $logger;
        $this->serializer = new Serializer($messageFactory);
    }

    public function bind(LoopInterface $eventLoop, $bindAddress="0.0.0.0:6881"){
        $factory = new DatagramFactory($eventLoop);

        $factory->createServer($bindAddress)->then(function(Socket $server) use ($bindAddress){
            $this->socket = $server;

            $this->logger->info("UdpServer bound", array(
                "address" => $bindAddress
            ));

            $server->on("message", function($buf, $address){
                try{
                    $this->acceptMessage($buf, $address);
                } catch(\Exception $exception){
                    $this->logger->error('Unhandled exception', array(
                        'exception' => $exception,
                        'address' => $address
                    ));

                    $this->emit("exception", array($this, $exception));
                }
            });
        });
    }

    /**
     * @param UdpConnectionRequest $in
     * @return DeferredPromise
     */
    public function connect(UdpConnectionRequest $in){
        do{
            $connectionId = openssl_random_pseudo_bytes(8);
        } while(array_key_exists($connectionId, $this->_connections));

        $this->_connections[$connectionId] = new Connection($connectionId, new DateTime());

        $reply = new UdpConnectionResponse($in);
        $reply->setConnectionId($connectionId);

        $this->emit("connect", array($this, $in));

        $deferred = new Deferred();
        $deferred->resolve($reply);

        return $deferred->promise();
    }

    public function announce(AnnounceRequest $announce){
        $deferred = new Deferred();

        # Use announce address if no peer address given
        if(!$announce->getIpv4())
            $announce->setIpv4($announce->getRequestEndpoint()->getIp());

        if(!$announce->getPort())
            $announce->setPort($announce->getRequestEndpoint()->getPort());

        $this->emit("announce", array($this, $announce, $deferred));

        return $deferred->promise();
    }

    public function scrape(ScrapeRequest $scrape)
    {
        $deferred = new Deferred();
        $this->emit("scrape", array($this, $scrape, $deferred));

        return $deferred->promise();
    }

    private function validateConnection(UdpConnection $connection){
        return array_key_exists($connection->getConnectionId(), $this->_connections);
    }

    public function acceptMessage($buf, $address)
    {
        list($connectionId, $transactionId, $inputPacket) = $this->serializer->decode($buf);
        $udpConnection = new UdpConnection($connectionId, $transactionId);

        $endpoint = Endpoint::fromString($address);
        $inputPacket->setRequestEndpoint($endpoint);

        if($inputPacket instanceof UdpConnectionRequest)
            $promise = $this->connect($inputPacket);
        elseif(!$this->validateConnection($udpConnection)){
            $promise = new Deferred();
            $promise->reject(new TrackerException($inputPacket,"Client not connected"));
        } else {
            # Heartbeat
            $this->_connections[$connectionId]->setLastHeartbeat(new DateTime());
        }

        if($inputPacket instanceof AnnounceRequest) {
            $promise = $this->announce($inputPacket);
        }elseif($inputPacket instanceof ScrapeRequest) {
            $promise = $this->scrape($inputPacket);
        }

        if(!isset($promise))
            throw new \InvalidArgumentException("Unknown request");

        # Trigger events
        $this->emit("input", array($this, $inputPacket));

        return $promise->then(
            function(TrackerResponse $response) use($udpConnection){
                $this->logger->notice("Sending response", array(
                    'type' => $response->getMessageType(),
                    'address' => $response->getRequest()->getRequestEndpoint()->toString()
                ));

                $this->send($udpConnection, $response);
            }, function (\Exception $e) use ($udpConnection, $address){
                if($e instanceof TrackerException) {
                    $trackerResponse = new ErrorResponse($e->getRequest(), $e->getMessage());
                    $this->send($udpConnection, $trackerResponse);
                }

                $this->logger->error($e->getMessage(), array(
                    'exception' => $e,
                    'address' => $address
                ));
            }
        );
    }

    protected function send(UdpConnection $connection, TrackerResponse $response){
        $address = $response->getRequest()->getRequestEndpoint()->toString();
        $buff = $this->serializer->encode($connection->getTransactionId(),$response);
        $this->socket->send($buff, $address);
    }
}