<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 11:46
 */

namespace Devristo\TorrentTracker\UdpServer;


use Akamon\MockeryCallableMock\MockeryCallableMock;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\UdpServer\Message\UdpAnnounceRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionResponse;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use React\EventLoop\Factory;
use React\Promise\Deferred;

class ServerTest extends \PHPUnit_Framework_TestCase {
    /** @var Logger */
    protected $logger;

    /** @var Server */
    protected $server;

    public function tearDown(){
        \Mockery::close();
    }

    public function setUp(){
        $loop = Factory::create();
        $this->logger = new Logger("Server test");
        $this->logger->pushHandler(new StreamHandler("php://output"));
        $this->server = new Server($this->logger);

        $this->server->bind($loop, "0.0.0.0:6881");
    }

    public function test_announce_without_connect(){
        $request = new UdpAnnounceRequest();
        $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));

        $onAnnounce = new MockeryCallableMock();
        $onAnnounce->shouldBeCalled()->never();

        $onResponse = new MockeryCallableMock();
        $onResponse->shouldBeCalled()->never();

        $onFail = new MockeryCallableMock();
        $onFail->shouldBeCalled()->once();

        $this->server->on("announce", $onAnnounce);
        $this->server->announce($request)->then($onResponse, $onFail);
    }

    public function test_announce(){
        $connectRequest = new UdpConnectionRequest();
        $connectRequest->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));
        $connectRequest->setConnectionId(hex2bin("0000041727101980"));
        $connectRequest->setTransactionId('aaaa');

        $onConnect = new MockeryCallableMock();
        $onConnect->shouldBeCalled()->once()->with($this->server, $connectRequest);

        $onResponse = new MockeryCallableMock();
        $onResponse->shouldBeCalled()->once();

        $this->server->on("connect", $onConnect);
        $this->server->connect($connectRequest)->then(function(UdpConnectionResponse $response) use($onResponse){
            $this->assertInstanceOf(UdpConnectionResponse::class, $response);
            $request = new UdpAnnounceRequest();
            $request->setRequestEndpoint(Endpoint::fromString("127.0.0.1:80"));
            $request->setTransactionId('bbbb');
            $request->setConnectionId($response->getConnectionId());

            $onAnnounce = new MockeryCallableMock();
            $onAnnounce->shouldBeCalled()->once()->with($this->server, $request, \Mockery::type(Deferred::class));

            $this->server->on("announce", $onAnnounce);
            $this->server->announce($request);
        })->then($onResponse);
    }
}
 