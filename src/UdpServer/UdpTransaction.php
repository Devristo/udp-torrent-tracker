<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-6-2014
 * Time: 18:38
 */

namespace Devristo\TorrentTracker\UdpServer;


class UdpTransaction {
    protected $connectionId;
    protected $transactionId;

    public function __construct($connectionId, $transactionId){
        $this->connectionId = $connectionId;
        $this->transactionId = $transactionId;
    }

    /**
     * @return mixed
     */
    public function getConnectionId()
    {
        return $this->connectionId;
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
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }
} 