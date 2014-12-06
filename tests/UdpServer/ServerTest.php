<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 11:46
 */

namespace Devristo\TorrentTracker\UdpServer;


use Akamon\MockeryCallableMock\MockeryCallableMock;
use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\AnnounceResponse;
use Devristo\TorrentTracker\Message\TrackerResponse;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\Tracker;
use Devristo\TorrentTracker\UdpServer\Message\UdpAnnounceRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionResponse;
use Mockery;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;

class ServerTest extends \PHPUnit_Framework_TestCase {
    /** @var Logger */
    protected $logger;

    /** @var Server */
    protected $server;

    /** @var Mockery\MockInterface|Tracker */
    protected $tracker;

    public function tearDown(){
        \Mockery::close();
    }

    public function setUp(){
        $loop = Factory::create();
        $this->logger = new Logger("Server test");
        $this->logger->pushHandler(new StreamHandler("php://output"));

        $this->tracker = Mockery::mock(Tracker::class);

        $this->server = new Server($this->tracker, $this->logger);
        $this->server->bind($loop, "0.0.0.0:6881");
    }

    public function test_announce_without_connect(){
        $request = new AnnounceRequest();
        $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));

        $errorResponse = $this->server->acceptMessage(new UdpTransaction('aaa', 'bbb'), $request);
        $this->assertInstanceOf(TrackerResponse::class, $errorResponse);
    }

    public function test_announce(){
        $connectRequest = new UdpConnectionRequest();
        $connectRequest->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));
        $connectRequest->setConnectionId(hex2bin("0000041727101980"));
        $connectRequest->setTransactionId('aaaa');

        $onConnect = new MockeryCallableMock('onConnect');
        $onConnect->shouldBeCalled()->once()->with($this->server, $connectRequest);

        $this->server->on("connect", $onConnect);

        $response = $this->server->connect($connectRequest);

        $this->assertInstanceOf(UdpConnectionResponse::class, $response);
        $request = new AnnounceRequest();
        $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));

        $udpConnection = new UdpTransaction($response->getConnectionId(), null);

        $announceResponse = new AnnounceResponse($request);
        $this->tracker->shouldReceive('announce')->andReturn($announceResponse);
        $this->assertEquals(
            $announceResponse,
            $this->server->acceptMessage($udpConnection, $request)
        );
    }
}
 