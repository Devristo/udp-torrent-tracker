<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 21:21
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;

class ScrapeResponse extends TrackerResponse{
    protected $stats = array();

    public function getMessageType()
    {
        return 'scrape';
    }

    public function addInfoHash($infoHash, $complete, $downloaded, $incomplete){
        $this->stats[$infoHash] = array(
            'complete' => $complete,
            'downloaded' => $downloaded,
            'incomplete' => $incomplete
        );
    }

    public function getStats()
    {
        return $this->stats;
    }
}