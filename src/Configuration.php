<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 6-12-2014
 * Time: 17:53
 */

namespace Devristo\TorrentTracker;


use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\ScrapeRequest;

class Configuration {

    /**
     * The announce interval in seconds
     * @var int
     */
    protected $announceInterval;

    /**
     * The invalidation factor
     * @var int
     */
    protected $invalidationFactor;

    /**
     * @var callable
     */
    protected $announceRequestConstructor;

    /**
     * @var callable
     */
    protected $scrapeRequestConstructor;

    /**
     * @var int
     */
    protected $maxAnnouncePeers = 200;

    public function __construct(){
        $this->announceRequestConstructor = function(){
            return new AnnounceRequest();
        };

        $this->scrapeRequestConstructor = function(){
            return new ScrapeRequest();
        };
    }

    /**
     * @return callable
     */
    public function getAnnounceRequestConstructor()
    {
        return $this->announceRequestConstructor;
    }

    /**
     * @param callable $announceRequestConstructor
     */
    public function setAnnounceRequestConstructor(callable $announceRequestConstructor)
    {
        $this->announceRequestConstructor = $announceRequestConstructor;
    }

    /**
     * @return callable
     */
    public function getScrapeRequestConstructor()
    {
        return $this->scrapeRequestConstructor;
    }

    /**
     * @param callable $scrapeRequestConstructor
     */
    public function setScrapeRequestConstructor($scrapeRequestConstructor)
    {
        $this->scrapeRequestConstructor = $scrapeRequestConstructor;
    }

    /**
     * @return int
     */
    public function getMaxAnnouncePeers()
    {
        return $this->maxAnnouncePeers;
    }

    /**
     * @param int $maxAnnouncePeers
     */
    public function setMaxAnnouncePeers($maxAnnouncePeers)
    {
        $this->maxAnnouncePeers = $maxAnnouncePeers;
    }

    /**
     * @return int
     */
    public function getAnnounceInterval()
    {
        return $this->announceInterval;
    }

    /**
     * @param int $announceInterval
     * @return Configuration
     */
    public function setAnnounceInterval($announceInterval)
    {
        $this->announceInterval = $announceInterval;
        return $this;
    }

    /**
     * @return int
     */
    public function getInvalidationFactor()
    {
        return $this->invalidationFactor;
    }

    /**
     * @param int $invalidationFactor
     * @return Configuration
     */
    public function setInvalidationFactor($invalidationFactor)
    {
        $this->invalidationFactor = $invalidationFactor;
        return $this;
    }
}