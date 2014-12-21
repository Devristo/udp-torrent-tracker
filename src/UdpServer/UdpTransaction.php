<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-6-2014
 * Time: 18:38
 */

namespace Devristo\TorrentTracker\UdpServer;


use Devristo\TorrentTracker\Model\Endpoint;

class UdpTransaction {
    protected $connectionId;
    protected $transactionId;

    public function __construct(Endpoint $endpoint, $connectionId, $transactionId){
        $this->endpoint = $endpoint;
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

    /**
     * @return Endpoint
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param Endpoint $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }
} 