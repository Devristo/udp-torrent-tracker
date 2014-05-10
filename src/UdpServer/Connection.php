<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 21:58
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\UdpServer;


class Connection {
    protected $connectionId;
    protected $lastHeartbeat;

    /**
     * @param mixed $connectionId
     */
    public function setConnectionId($connectionId)
    {
        $this->connectionId = $connectionId;
    }

    /**
     * @return mixed
     */
    public function getConnectionId()
    {
        return $this->connectionId;
    }

    /**
     * @param mixed $lastHeartbeat
     */
    public function setLastHeartbeat($lastHeartbeat)
    {
        $this->lastHeartbeat = $lastHeartbeat;
    }

    /**
     * @return mixed
     */
    public function getLastHeartbeat()
    {
        return $this->lastHeartbeat;
    }

    public function __construct($connectionId, $lastHeartbeat){
        $this->connectionId = $connectionId;
        $this->lastHeartbeat = $lastHeartbeat;
    }

}