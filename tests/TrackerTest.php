<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 13:40
 */

namespace Devristo\TorrentTracker;


use Akamon\MockeryCallableMock\MockeryCallableMock;
use Devristo\TorrentTracker\Model\ArrayRepository;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\Model\SwarmPeer;
use Devristo\TorrentTracker\Model\Torrent;
use Devristo\TorrentTracker\Model\TorrentRepositoryInterface;
use Devristo\TorrentTracker\Messages\UdpAnnounceRequest;
use Devristo\TorrentTracker\Messages\AnnounceResponse;
use Devristo\TorrentTracker\Messages\ConnectionRequest;
use Devristo\TorrentTracker\Messages\ConnectionResponse;
use Devristo\TorrentTracker\UdpServer\Server;
use Mockery\Matcher\Closure;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class TrackerTest extends \PHPUnit_Framework_TestCase {
    /** @var TorrentRepositoryInterface */
    protected $repository;

    /** @var  Tracker */
    protected $tracker;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Server */
    protected $udpServer;

    /** @var LoopInterface */
    protected $loop;

    public function tearDown(){
        \Mockery::close();
    }

    public function setUp(){
        $this->loop = Factory::create();
        $this->logger = new Logger('TrackerTest');
        $this->repository = new ArrayRepository();

        $this->udpServer = new Server($this->logger);
        $this->udpServer->bind($this->loop, "0.0.0.0:6881");

        $this->tracker = new Tracker($this->logger, $this->repository);
        $this->tracker->bind($this->udpServer);
    }

    public function test_udp_announce(){
        $infohash = str_repeat("\0", 20);
        $torrent = new Torrent();

        $torrent->setInfoHash($infohash);
        $torrent->setFileSize(1024);

        $this->repository->trackTorrent($torrent);

        $connectRequest = new ConnectionRequest();
        $connectRequest->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));
        $connectRequest->setConnectionId(hex2bin("0000041727101980"));
        $connectRequest->setTransactionId('aaaa');

        $this->udpServer->connect($connectRequest)->then(function(ConnectionResponse $response) use($infohash) {

            $request = new UdpAnnounceRequest();
            $request->setInfoHash($infohash);
            $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));
            $request->setTransactionId('bbbb');
            $request->setConnectionId($response->getConnectionId());

            $announceResponse = new MockeryCallableMock();
            $announceResponse->shouldBeCalled()->once()->with(new Closure(function(AnnounceResponse $response){
                $this->assertEquals(0, count($response->getPeers()));
                $this->assertEquals('bbbb', $response->getRequest()->getTransactionId());

                return true;
            }));

            $this->udpServer->announce($request)->then($announceResponse);
        });

        $connectRequest = new ConnectionRequest();
        $connectRequest->setRequestEndpoint(Endpoint::fromString("127.0.0.1:81"));
        $connectRequest->setConnectionId(hex2bin("0000041727101980"));
        $connectRequest->setTransactionId('cccc');

        $this->udpServer->connect($connectRequest)->then(function(ConnectionResponse $response) use($infohash){

            $request = new UdpAnnounceRequest();
            $request->setInfoHash($infohash);
            $request->setPeerId("A");
            $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:81"));
            $request->setTransactionId('dddd');
            $request->setConnectionId($response->getConnectionId());

            $announceResponse = new MockeryCallableMock();
            $announceResponse->shouldBeCalled()->once()->with(new Closure(function(AnnounceResponse $response){
                $this->assertEquals(1, count($response->getPeers()));
                $this->assertEquals('dddd', $response->getRequest()->getTransactionId());

                $peer = $response->getPeers()[0];
                $this->assertInstanceOf(SwarmPeer::class, $peer, "Peer should be a SwarmPeer");
                $this->assertEquals(80, $peer->getPort());

                return true;
            }));

            $this->udpServer->announce($request)->then($announceResponse);
        });
    }
}
 