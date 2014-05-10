<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:31
 */

namespace Devristo\TorrentTracker;


class Session {
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
} 