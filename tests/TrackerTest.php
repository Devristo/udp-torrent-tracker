<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 13:40
 */

namespace Devristo\TorrentTracker;


use Akamon\MockeryCallableMock\MockeryCallableMock;
use Devristo\TorrentTracker\Event\TrackerEvent;
use Devristo\TorrentTracker\Exceptions\TrackerException;
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
use Devristo\TorrentTracker\UdpServer\UdpTransaction;
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

        $this->tracker = new Tracker($this->repository);
        $this->udpServer = new Server($this->tracker, $this->logger);
        $this->udpServer->bind($this->loop, "0.0.0.0:6881");
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
        $this->tracker->announce($announce1, new Deferred());
        $this->tracker->announce($announce2, new Deferred());
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

        $response = $this->udpServer->connect($connectRequest);

        $request = new AnnounceRequest();
        $request->setInfoHash($infohash);
        $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));

        $udpConnection = new UdpTransaction($response->getConnectionId(), 'bbb');
        $announceResponse = $this->udpServer->acceptMessage($udpConnection, $request);
        $this->assertEquals(
            0, count($announceResponse->getPeers())
        );

        $connectRequest = new UdpConnectionRequest();
        $connectRequest->setRequestEndpoint(Endpoint::fromString("127.0.0.1:81"));
        $connectRequest->setConnectionId(hex2bin("0000041727101980"));
        $connectRequest->setTransactionId('cccc');

        $response = $this->udpServer->connect($connectRequest);

        $request = new AnnounceRequest();
        $request->setInfoHash($infohash);
        $request->setPeerId("A");
        $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:81"));

        $udpConnection = new UdpTransaction($response->getConnectionId(), 'dddd');
        $response = $this->udpServer->acceptMessage($udpConnection, $request);

        $this->assertEquals(1, count($response->getPeers()));

        $peer = $response->getPeers()[0];
        $this->assertInstanceOf(SwarmPeer::class, $peer, "Peer should be a SwarmPeer");
        $this->assertEquals(80, $peer->getPort());

        return true;
    }


    public function test_announce_cancel(){
        $announce = $this->create_announce(0,0);

        $this->tracker->on("announce", function(TrackerEvent $event, AnnounceRequest $request, AnnounceDifference $delta){
            $event->cancel("Ratio limit enforced!");
        });

        try {
            $this->tracker->announce($announce);
        } catch(TrackerException $e){
            $this->assertEquals('Ratio limit enforced!', $e->getMessage());
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
 