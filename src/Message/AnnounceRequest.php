<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:36
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;

class AnnounceRequest implements AnnounceRequestInterface
{
    const EVENT_NONE = 0;
    const EVENT_COMPLETED = 1;
    const EVENT_STARTED = 2;
    const EVENT_STOPPED = 3;

    protected $infoHash;
    protected $peerId;
    protected $downloaded;
    protected $left;
    protected $uploaded;
    protected $event;
    protected $ipv4;
    protected $key;
    protected $num_want;
    protected $port;
    protected $announceTime;
    protected $expirationTime;
    protected $downloadSpeed;
    protected $uploadSpeed;

    public function setEvent($event){
        $this->event = $event;
    }

    public function getRequestUri()
    {
        return $this->requestString;
    }

    public function getEvent()
    {
        return $this->event;
    }

    protected $requestString = null;

    public function setDownloaded($downloaded)
    {
        $this->downloaded = $downloaded;
    }

    public function getDownloaded()
    {
        return $this->downloaded;
    }

    public function setInfoHash($infoHash)
    {
        $this->infoHash = $infoHash;
    }

    public function getInfoHash()
    {
        return $this->infoHash;
    }

    public function setIpv4($ipv4)
    {
        $this->ipv4 = $ipv4;
    }

    public function getIpv4()
    {
        return $this->ipv4;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setLeft($left)
    {
        $this->left = $left;
    }

    public function getLeft()
    {
        return $this->left;
    }

    public function setNumWant($num_want)
    {
        $this->num_want = $num_want;
    }

    public function getNumWant()
    {
        return $this->num_want;
    }

    public function setPeerId($peerId)
    {
        $this->peerId = $peerId;
    }

    public function getPeerId()
    {
        return $this->peerId;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setUploaded($uploaded)
    {
        $this->uploaded = $uploaded;
    }

    public function getUploaded()
    {
        return $this->uploaded;
    }

    public function setRequestUri($value){
        $this->requestString = $value;
    }

    public function getMessageType(){
        return 'announce';
    }

    /**
     * @return \DateTime
     */
    public function getAnnounceTime()
    {
        return $this->announceTime;
    }

    public function setAnnounceTime(\DateTime $dateTime)
    {
        $this->announceTime = $dateTime;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(\DateTime $dateTime)
    {
        $this->expirationTime = $dateTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUploadSpeed()
    {
        return $this->uploadSpeed;
    }

    /**
     * @param mixed $uploadSpeed
     */
    public function setUploadSpeed($uploadSpeed)
    {
        $this->uploadSpeed = $uploadSpeed;
    }

    /**
     * @return mixed
     */
    public function getDownloadSpeed()
    {
        return $this->downloadSpeed;
    }

    /**
     * @param mixed $downloadSpeed
     */
    public function setDownloadSpeed($downloadSpeed)
    {
        $this->downloadSpeed = $downloadSpeed;
    }
}