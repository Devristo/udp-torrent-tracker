<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 16-5-2014
 * Time: 22:29
 */

namespace Devristo\TorrentTracker\Model;


use Devristo\TorrentTracker\Message\AnnounceRequestInterface;

class AnnounceDifference
{
    protected $downloaded;
    protected $uploaded;
    protected $time;

    protected $currentRequest;
    protected $previousRequest;

    public function __construct(AnnounceRequestInterface $currentRequest, AnnounceRequestInterface $previousRequest=null){
        $this->currentRequest = $currentRequest;
        $this->previousRequest = $previousRequest;

        $this->downloaded = 0;
        $this->uploaded = 0;
        $this->time = 0;

        if($previousRequest) {
            $this->downloaded = max(0, $currentRequest->getDownloaded() - $previousRequest->getDownloaded());
            $this->uploaded = max(0, $currentRequest->getUploaded() - $previousRequest->getUploaded());
            $this->time = $currentRequest->getAnnounceTime()->getTimestamp() - $previousRequest->getAnnounceTime()->getTimestamp();
        }
    }

    public function getDownloaded(){
        return $this->downloaded;
    }

    public function getUploaded(){
        return $this->uploaded;
    }

    public function getSeconds(){
        return $this->time;
    }

    /**
     * @return AnnounceRequestInterface
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * @return AnnounceRequestInterface
     */
    public function getPreviousRequest()
    {
        return $this->previousRequest;
    }
} 