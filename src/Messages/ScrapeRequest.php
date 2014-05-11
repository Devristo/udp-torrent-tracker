<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:36
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Messages;


use Devristo\TorrentTracker\Exceptions\ProtocolViolationException;
use Devristo\TorrentTracker\Messages\BaseRequest;

class ScrapeRequest extends BaseRequest {
    protected $infoHashes = array();

    public function getInfoHashes(){
        return $this->infoHashes;
    }

    public function setInfoHashes(array $infoHashes)
    {
        $this->infoHashes = $infoHashes;
    }
}