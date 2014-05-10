<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 19:20
 */
namespace Devristo\TorrentTracker;

use Evenement\EventEmitterInterface;
use React\EventLoop\LoopInterface;

interface ServerInterface extends EventEmitterInterface
{
    public function bind(LoopInterface $eventLoop);
}