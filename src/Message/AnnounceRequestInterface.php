<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 6-12-2014
 * Time: 18:08
 */
namespace Devristo\TorrentTracker\Message;

interface AnnounceRequestInterface extends TrackerRequest
{
    public function setEvent($event);

    /**
     * @return null
     */
    public function getRequestUri();

    /**
     * @return mixed
     */
    public function getEvent();

    /**
     * @param mixed $downloaded
     */
    public function setDownloaded($downloaded);

    /**
     * @return mixed
     */
    public function getDownloaded();

    /**
     * @param mixed $infoHash
     */
    public function setInfoHash($infoHash);

    /**
     * @return mixed
     */
    public function getInfoHash();

    /**
     * @param mixed $ipv4
     */
    public function setIpv4($ipv4);

    /**
     * @return mixed
     */
    public function getIpv4();

    /**
     * @param mixed $key
     */
    public function setKey($key);

    /**
     * @return mixed
     */
    public function getKey();

    /**
     * @param mixed $left
     */
    public function setLeft($left);

    /**
     * @return mixed
     */
    public function getLeft();

    /**
     * @param mixed $num_want
     */
    public function setNumWant($num_want);

    /**
     * @return mixed
     */
    public function getNumWant();

    /**
     * @param mixed $peerId
     */
    public function setPeerId($peerId);

    /**
     * @return mixed
     */
    public function getPeerId();

    /**
     * @param mixed $port
     */
    public function setPort($port);

    /**
     * @return mixed
     */
    public function getPort();

    /**
     * @param mixed $uploaded
     */
    public function setUploaded($uploaded);

    /**
     * @return mixed
     */
    public function getUploaded();

    public function setRequestUri($value);

    /**
     * @return \DateTime
     */
    public function getAnnounceTime();
    public function setAnnounceTime(\DateTime $dateTime);

    /**
     * @return \DateTime
     */
    public function getExpirationTime();
    public function setExpirationTime(\DateTime $dateTime);

    /**
     * @return mixed
     */
    public function getUploadSpeed();

    /**
     * @param mixed $uploadSpeed
     */
    public function setUploadSpeed($uploadSpeed);

    /**
     * @return mixed
     */
    public function getDownloadSpeed();

    /**
     * @param mixed $downloadSpeed
     */
    public function setDownloadSpeed($downloadSpeed);
}