<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 16-5-2014
 * Time: 22:29
 */

namespace Devristo\TorrentTracker\Model;


use Devristo\TorrentTracker\Message\AnnounceRequest;

class AnnounceDifference
{
    protected $downloaded;
    protected $uploaded;

    public function __construct($downloaded=0, $uploaded=0){
        $this->downloaded = $downloaded;
        $this->uploaded = $uploaded;
    }

    public function getDownloaded(){
        return $this->downloaded;
    }

    public function getUploaded(){
        return $this->uploaded;
    }

    public static function diff(AnnounceRequest $prev, AnnounceRequest $current){
        $downloaded = max(0, $current->getDownloaded() - $prev->getDownloaded());
        $uploaded = max(0, $current->getUploaded() - $prev->getUploaded());

        return new static($downloaded, $uploaded);
    }
} 