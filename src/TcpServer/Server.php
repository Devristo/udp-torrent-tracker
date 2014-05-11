<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 19:21
 */

namespace Devristo\TorrentTracker\TcpServer;


use Devristo\TorrentTracker\Messages\AnnounceRequest;
use Devristo\TorrentTracker\Messages\BaseResponse;
use Devristo\TorrentTracker\Messages\ScrapeRequest;
use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\ServerInterface;
use Evenement\EventEmitter;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Request;
use React\Http\Response;
use React\Promise\Deferred;

class Server extends EventEmitter implements ServerInterface{
    protected $conversion;
    protected $messageHandlers;
    protected $logger;

    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
        $this->conversion = new Serializer();

        $this->messageHandlers = array(
            "announce" => array($this, 'announce'),
            "scrape" => array($this, 'scrape')
        );
    }

    public function bind(LoopInterface $eventLoop, $address="0.0.0.0:6881")
    {
        $socket = new \React\Socket\Server($eventLoop);
        $endpoint = Endpoint::fromString($address);
        $socket->listen($endpoint->getPort(), $endpoint->getIp());

        $this->logger->info("TcpServer bound", array(
            "address" => $address
        ));

        $http = new \React\Http\Server($socket);
        $http->on('request', function(Request $request, Response $httpResponse){
            $this->acceptMessage($request)->then(
                function(BaseResponse $trackerResponse) use($httpResponse){
                    $httpResponse->writeHead(200, array('Content-Type' => 'text/plain'));
                    $httpResponse->end($this->conversion->encode($trackerResponse));
                }
            );
        });
    }

    public function announce(AnnounceRequest $announce)
    {
        $deferred = new Deferred();
        $this->emit("announce", array($this, $announce, $deferred));
        return $deferred->promise();
    }

    public function scrape(ScrapeRequest $scrape)
    {
        $deferred = new Deferred();
        $this->emit("announce", array($this, $scrape, $deferred));
        return $deferred->promise();
    }

    public function acceptMessage(Request $request) {
        $promise = (new Deferred())->reject('Unknown tracker error');

        try {
            $request = $this->conversion->decode($request);
            $handler = $this->messageHandlers[$request->getMessageType()];
            $promise = call_user_func($handler, $request);
        } catch (\Exception $e){
            $this->logger->error("Cannot handle request", array(
                'exception' => $e
            ));
            $promise = (new Deferred())->reject('Cannot handle request');
        }

        return $promise;
    }
}