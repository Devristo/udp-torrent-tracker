<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 13:40
 */

namespace Devristo\TorrentTracker;


use Akamon\MockeryCallableMock\MockeryCallableMock;
use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\AnnounceResponse;
use Devristo\TorrentTracker\Model\AnnounceDifference;
use Devristo\TorrentTracker\Repository\ArrayRepository;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\Model\SwarmPeer;
use Devristo\TorrentTracker\Model\Torrent;
use Devristo\TorrentTracker\UdpServer\Message\UdpAnnounceRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionResponse;
use Devristo\TorrentTracker\UdpServer\Server;
use Mockery\Matcher\Closure;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;

class TrackerTest extends \PHPUnit_Framework_TestCase {
    /** @var \Devristo\TorrentTracker\Repository\TorrentRepositoryInterface */
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

    private function create_announce($downloaded, $uploaded, $peerId=null, $infoHash=null){
        $infoHash = $infoHash ?: str_repeat("\0", 20);
        $peerId = $peerId ?: str_repeat('a', 20);

        $announce = new AnnounceRequest();
        $announce->setInfoHash($infoHash);
        $announce->setPeerId($peerId);
        $announce->setDownloaded($downloaded);
        $announce->setUploaded($uploaded);

        return $announce;
    }

    public function test_announce_diff(){
        $announce1 = $this->create_announce(0, 0);
        $announce2 = $this->create_announce(100, 200);

        $server = $this->udpServer;

        $announceListener = new MockeryCallableMock();
        $announceListener->shouldBeCalled()->with(
            \Mockery::type(TrackerEvent::class),
            $announce1,
            \Mockery::any()
        )->once()->ordered();

        $announceListener->shouldBeCalled()->with(
            \Mockery::type(TrackerEvent::class),
            $announce2,
            new Closure(function(AnnounceDifference $diff){
                return $diff->getDownloaded() == 100 && $diff->getUploaded() == 200;
            })
        )->once()->ordered();

        $this->tracker->on("announce", $announceListener);
        $this->tracker->announce($server, $announce1, new Deferred());
        $this->tracker->announce($server, $announce2, new Deferred());
    }

    public function test_udp_announce(){
        $infohash = str_repeat("\0", 20);
        $torrent = new Torrent();

        $torrent->setInfoHash($infohash);
        $torrent->setFileSize(1024);

        $connectRequest = new UdpConnectionRequest();
        $connectRequest->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));
        $connectRequest->setConnectionId(hex2bin("0000041727101980"));
        $connectRequest->setTransactionId('aaaa');

        $this->udpServer->connect($connectRequest)->then(function(UdpConnectionResponse $response) use($infohash) {

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

        $connectRequest = new UdpConnectionRequest();
        $connectRequest->setRequestEndpoint(Endpoint::fromString("127.0.0.1:81"));
        $connectRequest->setConnectionId(hex2bin("0000041727101980"));
        $connectRequest->setTransactionId('cccc');

        $this->udpServer->connect($connectRequest)->then(function(UdpConnectionResponse $response) use($infohash){

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
 