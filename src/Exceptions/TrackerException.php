<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 11-5-2014
 * Time: 18:18
 */

namespace Devristo\TorrentTracker\Exceptions;


use Devristo\TorrentTracker\Message\TrackerRequest;

class TrackerException extends \Exception{
    protected $request;

    public function __construct(TrackerRequest $request, $message = "", $code = 0, \Exception $previous = null){
        parent::__construct($message, $code, $previous);
        $this->request = $request;
    }

    public function getRequest(){
        return $this->request;
    }
} 