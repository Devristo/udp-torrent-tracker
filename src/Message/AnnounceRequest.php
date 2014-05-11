<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 20:36
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;

class AnnounceRequest extends TrackerRequest{
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
    protected $extensions;

    protected $credentials = null;

    public function setEvent($event){
        $this->event = $event;
    }

    /**
     * @return null
     */
    public function getRequestString()
    {
        return $this->requestString;
    }

    /**
     * @return mixed
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return null
     */
    public function getCredentials()
    {
        return $this->credentials;
    }
    protected $requestString = null;

    /**
     * @param mixed $downloaded
     */
    public function setDownloaded($downloaded)
    {
        $this->downloaded = $downloaded;
    }

    /**
     * @return mixed
     */
    public function getDownloaded()
    {
        return $this->downloaded;
    }

    /**
     * @param mixed $infoHash
     */
    public function setInfoHash($infoHash)
    {
        $this->infoHash = $infoHash;
    }

    /**
     * @return mixed
     */
    public function getInfoHash()
    {
        return $this->infoHash;
    }

    /**
     * @param mixed $ipv4
     */
    public function setIpv4($ipv4)
    {
        $this->ipv4 = $ipv4;
    }

    /**
     * @return mixed
     */
    public function getIpv4()
    {
        return $this->ipv4;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $left
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * @return mixed
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param mixed $num_want
     */
    public function setNumWant($num_want)
    {
        $this->num_want = $num_want;
    }

    /**
     * @return mixed
     */
    public function getNumWant()
    {
        return $this->num_want;
    }

    /**
     * @param mixed $peerId
     */
    public function setPeerId($peerId)
    {
        $this->peerId = $peerId;
    }

    /**
     * @return mixed
     */
    public function getPeerId()
    {
        return $this->peerId;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $uploaded
     */
    public function setUploaded($uploaded)
    {
        $this->uploaded = $uploaded;
    }

    /**
     * @return mixed
     */
    public function getUploaded()
    {
        return $this->uploaded;
    }

    public function setRequestString($value){
        $this->requestString = $value;
    }

    public function setCredentials(AuthenticationExtension $credentials){
        $this->credentials = $credentials;
    }

    public function getMessageType(){
        return 'announce';
    }
}