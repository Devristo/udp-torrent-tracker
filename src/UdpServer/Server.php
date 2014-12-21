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
use Devristo\TorrentTracker\Configuration;
use Devristo\TorrentTracker\Exceptions\TrackerException;
use Devristo\TorrentTracker\Message\TrackerRequest;
use Devristo\TorrentTracker\Message\TrackerResponse;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\ServerInterface;
use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Tracker;
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
use React\Promise\PromiseInterface;

class Server extends EventEmitter
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /** @var  Socket */
    protected $socket;

    protected $connectionTimeOut = 60;

    /**
     * @var Connection[]
     */
    protected $_connections = array();

    public function __construct(Tracker $tracker, Configuration $configuration, LoggerInterface $logger){
        $this->tracker = $tracker;
        $this->logger = $logger;
        $this->serializer = new Serializer($configuration);
    }

    public function bind(LoopInterface $eventLoop, $bindAddress="0.0.0.0:6881"){
        $factory = new DatagramFactory($eventLoop);

        $eventLoop->addPeriodicTimer($this->connectionTimeOut / 2, array($this, 'cleanupConnections'));

        $bindOnMessage = function(Socket $server) use ($bindAddress){
            $this->socket = $server;

            $this->logger->info("UdpServer bound", array(
                "address" => $bindAddress
            ));

            $server->on("message", array($this, 'acceptBuffer'));
        };

        $factory->createServer($bindAddress)->then($bindOnMessage);
    }

    public function cleanupConnections(){
        $toDelete = array();
        foreach($this->_connections as $id => $heartbeat){
            if($heartbeat + $this->connectionTimeOut < time())
                $toDelete[] = $heartbeat;
        }

        if(count($toDelete))
            $this->logger->info('Cleaning up connections', array('amount' => count($toDelete)));

        foreach($toDelete as $id)
            unset($this->_connections[$id]);
    }

    /**
     * @param UdpConnectionRequest $in
     * @return UdpConnectionResponse
     */
    public function connect(UdpConnectionRequest $in){
        do{
            $connectionId = openssl_random_pseudo_bytes(8);
        } while(array_key_exists($connectionId, $this->_connections));

        $this->_connections[$connectionId] = new Connection($connectionId, new DateTime());

        $reply = new UdpConnectionResponse($in);
        $reply->setConnectionId($connectionId);

        $this->emit("connect", array($this, $in));

        return $reply;
    }

    protected function ensureValidConnection(UdpTransaction $transaction, TrackerRequest $request){
        if($request instanceof UdpConnectionRequest)
            return true;
        else return array_key_exists($transaction->getConnectionId(), $this->_connections);
    }

    /**
     * @param UdpTransaction $udpConnection
     * @param TrackerRequest $inputPacket
     * @return PromiseInterface
     */
    public function acceptMessage(UdpTransaction $udpConnection, TrackerRequest $inputPacket){
        $isConnect = $inputPacket instanceof UdpConnectionRequest;

        try {
            if ($isConnect) {
                $response = $this->connect($inputPacket);
            } elseif ($this->ensureValidConnection($udpConnection, $inputPacket)) {
                $this->_connections[$udpConnection->getConnectionId()]->setLastHeartbeat(time());

                if ($inputPacket instanceof UdpConnectionRequest)
                    $response = $this->connect($inputPacket);
                elseif ($inputPacket instanceof AnnounceRequest)
                    $response = $this->tracker->announce($inputPacket);
                elseif ($inputPacket instanceof ScrapeRequest)
                    $response = $this->tracker->scrape($inputPacket);
                else
                    throw new \InvalidArgumentException("Unknown request");

                # Trigger events
                $this->emit("input", array($this, $inputPacket));
            } else {
                throw new TrackerException($inputPacket, "Client not connected");
            }

            $this->send($udpConnection, $response);

            return $response;
        } catch(TrackerException $e){

            $this->logger->error($e->getMessage(), array(
                'exception' => $e,
                'address' => $udpConnection->getEndpoint()
            ));

            $trackerResponse = new ErrorResponse($e->getRequest(), $e->getMessage());
            $this->send($udpConnection, $trackerResponse);
            return $trackerResponse;
        }
    }

    public function acceptBuffer($buf, $address) {
        $endpoint = Endpoint::fromString($address);

        try {
            /**
             * @var $connectionId string
             * @var $transactionId int
             * @var $inputPacket TrackerRequest
             */
            list($connectionId, $transactionId, $inputPacket) = $this->serializer->decode($buf);
            $udpConnection = new UdpTransaction($endpoint, $connectionId, $transactionId);
            $this->acceptMessage($udpConnection, $inputPacket);
        } catch(\Exception $exception){
            $this->logger->error('Unhandled exception', array(
                'exception' => $exception,
                'address' => $address
            ));

            $this->emit("exception", array($this, $exception));

            $trackerResponse = new ErrorResponse(null, $exception->getMessage());
            $this->send(new UdpTransaction($endpoint, 0, 0), $trackerResponse);
            return $trackerResponse;
        }
    }

    protected function send(UdpTransaction $connection, TrackerResponse $response){
        $address = (string) $connection->getEndpoint();

        $buff = $this->serializer->encode($connection->getTransactionId(), $response);
        $this->socket->send($buff, $address);

        $this->logger->notice("Response send", array(
            'type' => $response->getMessageType(),
            'address' => $address
        ));

    }
}