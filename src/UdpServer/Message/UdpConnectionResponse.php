<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\UdpServer\Message;

use Devristo\TorrentTracker\Message\TrackerResponse;

class UdpConnectionResponse extends TrackerResponse{
    protected $transactionId;
    protected $connectionId;

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

    public function isOpenHandshake(){
        return $this->connectionId == ("000041727101980");
    }

    public function getMessageType()
    {
        return 'connect';
    }
}