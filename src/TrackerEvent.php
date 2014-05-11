<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 11-5-2014
 * Time: 18:50
 */

namespace Devristo\TorrentTracker;


use Devristo\TorrentTracker\Message\TrackerRequest;

class TrackerEvent {
    protected $request;
    protected $error;
    protected $canceled = false;

    public function cancel($errorReason){
        $this->canceled = true;
        $this->error = $errorReason;
    }

    public function isCanceled(){
        return $this->canceled;
    }

    public function getError()
    {
        return $this->error;
    }

    public function __construct(TrackerRequest $request){
        $this->request = $request;
    }
} 