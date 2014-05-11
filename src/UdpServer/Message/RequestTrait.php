<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 11-5-2014
 * Time: 17:59
 */

namespace Devristo\TorrentTracker\UdpServer\Message;


trait RequestTrait {
    protected $connectionId;
    protected $transactionId;

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

} 