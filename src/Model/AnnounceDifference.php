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

    public function __construct($downloaded=0, $uploaded=0, $time=0){
        $this->downloaded = $downloaded;
        $this->uploaded = $uploaded;
        $this->time = $time;
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

    public static function diff(AnnounceRequestInterface $prev, AnnounceRequestInterface $current){
        $downloaded = max(0, $current->getDownloaded() - $prev->getDownloaded());
        $uploaded = max(0, $current->getUploaded() - $prev->getUploaded());
        $time = $current->getAnnounceTime()->getTimestamp() - $prev->getAnnounceTime()->getTimestamp();

        return new static($downloaded, $uploaded, $time);
    }
} 