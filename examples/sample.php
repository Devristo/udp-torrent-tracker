<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 10-5-2014
 * Time: 18:35
 */

use Devristo\TorrentTracker\Model\ArrayRepository;
use Devristo\TorrentTracker\UdpServer\Server as UdpServer;
use Devristo\TorrentTracker\TcpServer\Server as TcpServer;
use Devristo\TorrentTracker\Tracker;
use Monolog\Logger;
use React\EventLoop\Factory;

require_once __DIR__.'/../vendor/autoload.php';

$loop = Factory::create();
$logger = new Logger('TrackerTest');
$repository = new ArrayRepository();

$udpServer = new UdpServer($logger);
$udpServer->bind($loop, "0.0.0.0:6881");

$tcpServer = new TcpServer($logger);
$tcpServer->bind($loop);

$tracker = new Tracker($logger, $repository);
$tracker->bind($udpServer);
$tracker->bind($tcpServer);

$loop->run();
