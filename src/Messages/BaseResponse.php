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

    public function __construct(BaseRequest $request)
    {
        $this->request = $request;
    }
    /**
     * @return BaseRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param BaseRequest $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    abstract public function getMessageType();

} 