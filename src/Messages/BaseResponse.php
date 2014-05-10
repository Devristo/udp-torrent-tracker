<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 13:25
 */

namespace Devristo\TorrentTracker\Messages;


abstract class BaseResponse {
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    abstract public function getMessageType();

} 