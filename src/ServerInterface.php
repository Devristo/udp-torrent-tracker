<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 19:20
 */
namespace Devristo\TorrentTracker;


use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\ScrapeRequest;
use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;

interface ServerInterface extends EventEmitterInterface
{
    public function bind(LoopInterface $eventLoop, $bindAddress = "0.0.0.0:6881");

    public function announce(AnnounceRequest $announce);

    public function scrape(ScrapeRequest $scrape);
}