<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 22:07
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Messages;


class ErrorResponse extends BaseResponse {
    protected $action = 3;

    protected $message;

    public function __construct(Request $request, $message){
        parent::__construct($request);
        $this->setMessage($message);
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function getMessageType(){
        return 'error';
    }
}