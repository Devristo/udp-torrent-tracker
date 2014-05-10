<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 21:21
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Messages;

class ScrapeResponse extends BaseResponse{
    protected $transactionId;
    protected $connectionId;

    protected $leechers = array();
    protected $seeders = array();
    protected $completed = array();

    /**
     * @param array $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    /**
     * @return array
     */
    public function getCompleted()
    {
        return $this->completed;
    }

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
     * @param mixed $leechers
     */
    public function setLeechers(array $leechers)
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
    public function setSeeders(array $seeders)
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

    /**
     * @param mixed $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getMessageType()
    {
        return 'scrape';
    }
}