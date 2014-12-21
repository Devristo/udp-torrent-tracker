<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 10-5-2014
 * Time: 18:35
 */

use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\ScrapeRequest;
use Devristo\TorrentTracker\Model\AnnounceDifference;
use Devristo\TorrentTracker\Repository\ArrayRepository;
use Devristo\TorrentTracker\Event\TrackerEvent;
use Devristo\TorrentTracker\UdpServer\Server as UdpServer;
use Devristo\TorrentTracker\TcpServer\Server as TcpServer;
use Devristo\TorrentTracker\Tracker;
use Monolog\Logger;
use React\EventLoop\Factory;

require_once __DIR__.'/../vendor/autoload.php';

$loop = Factory::create();
$logger = new Logger('TrackerTest');
$repository = new ArrayRepository();

$tracker = new Tracker($repository);
$udpServer = new UdpServer($tracker, $logger);
$udpServer->bind($loop, "0.0.0.0:6881");

$tracker->on('announce', function(TrackerEvent $event, AnnounceRequest $request, AnnounceDifference $diff) use($logger){
    $logger->warning("New announce received", array(
        'client' => bin2hex($request->getPeerId()),
        'url' => $request->getRequestUri(),
        'dDown' => $diff->getDownloaded(),
        'dUp' => $diff->getUploaded()
    ));
});

$loop->run();
