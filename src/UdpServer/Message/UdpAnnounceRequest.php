<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:36
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\UdpServer\Message;

use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\UdpServer\Message\RequestTrait;

class UdpAnnounceRequest extends AnnounceRequest{
    use RequestTrait;
}