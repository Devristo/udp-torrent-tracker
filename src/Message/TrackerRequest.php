<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:38
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;


use Devristo\TorrentTracker\Model\Endpoint;

abstract class TrackerRequest
{
    const MESSAGE_TYPE_CONNECT = "connect";
    const MESSAGE_TYPE_ANNOUNCE = "announce";
    const MESSAGE_TYPE_SCRAPE = "scrape";

    protected $requestEndpoint;


    /**
     * @return mixed
     */
    abstract public function getMessageType();

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