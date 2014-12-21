<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:38
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;


interface TrackerRequest
{
    const MESSAGE_TYPE_CONNECT = "connect";
    const MESSAGE_TYPE_ANNOUNCE = "announce";
    const MESSAGE_TYPE_SCRAPE = "scrape";
}