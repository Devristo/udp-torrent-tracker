<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 19:21
 */

namespace Devristo\TorrentTracker\TcpServer;


use Devristo\TorrentTracker\Model\Endpoint;
use Devristo\TorrentTracker\ServerInterface;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Http\Request;
use React\Http\Response;

class Server extends EventEmitter implements ServerInterface{
    public function bind(LoopInterface $eventLoop, $address="0.0.0.0:6881")
    {
        $socket = new \React\Socket\Server($eventLoop);
        $endpoint = Endpoint::fromString($address);
        $socket->listen($endpoint->getPort(), $endpoint->getIp());

        $http = new \React\Http\Server($socket);
        $http->on('request', function (Request $request, Response $response) {
            $request->getHeaders()->

            $response->writeHead(200, array('Content-Type' => 'text/plain'));
            $response->end("Hello World!\n");
        });
    }
}