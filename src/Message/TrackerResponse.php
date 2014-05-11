<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 13:25
 */

namespace Devristo\TorrentTracker\Message;


abstract class TrackerResponse {
    protected $request;

    public function __construct(TrackerRequest $request)
    {
        $this->request = $request;
    }
    /**
     * @return TrackerRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param TrackerRequest $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    abstract public function getMessageType();

} 