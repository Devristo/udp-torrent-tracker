<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 10-5-2014
 * Time: 18:35
 */

use Devristo\TorrentTracker\Model\ArrayRepository;
use Devristo\TorrentTracker\UdpServer\Server;
use Devristo\TorrentTracker\Tracker;
use Monolog\Logger;
use React\EventLoop\Factory;

require_once __DIR__.'/../vendor/autoload.php';

$loop = Factory::create();
$logger = new Logger('TrackerTest');
$repository = new ArrayRepository();

$udpServer = new Server($logger);
$udpServer->bind($loop, "0.0.0.0:6881");

$tracker = new Tracker($logger, $repository);
$tracker->bind($udpServer);

$loop->run();
