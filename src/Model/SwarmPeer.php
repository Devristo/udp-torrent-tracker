<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 22:12
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Model;


class SwarmPeer {
    protected $ip;
    protected $port;

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    public function __construct($ip, $port){
        $this->ip = $ip;
        $this->port = (int) $port;
    }
}