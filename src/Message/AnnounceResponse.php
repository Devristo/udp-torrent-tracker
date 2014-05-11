<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 21:21
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;


class AnnounceResponse extends TrackerResponse {
    /**
     * @var \Devristo\TorrentTracker\Model\SwarmPeer[]
     */
    protected $peers = array();

    protected $interval = 15;
    protected $leechers = 0;
    protected $seeders = 0;


    /**
     * @param mixed $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return mixed
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param mixed $leechers
     */
    public function setLeechers($leechers)
    {
        $this->leechers = $leechers;
    }

    /**
     * @return mixed
     */
    public function getLeechers()
    {
        return $this->leechers;
    }

    /**
     * @param mixed $seeders
     */
    public function setSeeders($seeders)
    {
        $this->seeders = $seeders;
    }

    /**
     * @return mixed
     */
    public function getSeeders()
    {
        return $this->seeders;
    }

    public function setPeers(array $peers){
        $this->peers = $peers;
    }

    public function getPeers()
    {
        return $this->peers;
    }

    public function getMessageType()
    {
        return 'announce';
    }
}