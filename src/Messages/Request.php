<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:38
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Messages;


use Devristo\TorrentTracker\Model\Endpoint;

abstract class Request
{
    const MESSAGE_TYPE_CONNECT = "connect";
    const MESSAGE_TYPE_ANNOUNCE = "announce";
    const MESSAGE_TYPE_SCRAPE = "scrape";

    protected $connectionId;
    protected $messageType;
    protected $transactionId;

    protected $requestEndpoint;

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

    /**
     * @return mixed
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @param mixed $messageType
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;
    }

    public function setRequestEndpoint(Endpoint $endpoint)
    {
        $this->requestEndpoint = $endpoint;
    }

    /**
     * @return Endpoint
     */
    public function getRequestEndpoint(){
        return $this->requestEndpoint;
    }

}