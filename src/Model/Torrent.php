<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 14:25
 */

namespace Devristo\TorrentTracker\Model;


class Torrent implements TorrentInterface{
    protected $infoHash;
    protected $fileSize;

    /**
     * @return mixed
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @param mixed $fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
    }
    /**
     * @return mixed
     */
    public function getInfoHash()
    {
        return $this->infoHash;
    }

    /**
     * @param mixed $infoHash
     */
    public function setInfoHash($infoHash)
    {
        $this->infoHash = $infoHash;
    }

}