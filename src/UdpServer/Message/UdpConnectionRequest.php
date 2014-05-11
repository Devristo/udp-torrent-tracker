<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\UdpServer\Message;


use Devristo\TorrentTracker\Message\TrackerRequest;


class UdpConnectionRequest extends TrackerRequest{
    use RequestTrait;

    /**
     * @return mixed
     */
    public function getMessageType()
    {
        return 'connect';
    }
}