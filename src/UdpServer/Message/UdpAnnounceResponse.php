<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 21:21
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\UdpServer\Message;


use Devristo\TorrentTracker\Message\AnnounceResponse;
use Devristo\TorrentTracker\UdpServer\Message\RequestTrait;

class UdpAnnounceResponse extends AnnounceResponse {
    use RequestTrait;

}