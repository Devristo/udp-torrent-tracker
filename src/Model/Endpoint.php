<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 10-5-2014
 * Time: 20:17
 */

namespace Devristo\TorrentTracker\Model;


class Endpoint {
    protected $ip;
    protected $port;

    public function __construct($ip, $port){
        $this->setIp($ip);
        $this->setPort($port);
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    public function __toString(){
        return sprintf("%s:%d", $this->getIp(), $this->getPort());
    }

    public static function fromString($string){
        $dcolonRPos = strrpos($string, ':');

        if($dcolonRPos < 1 || $dcolonRPos >= strlen($string) - 1)
            throw new \BadMethodCallException("Invalid format");

        $port = intval(substr($string, $dcolonRPos+1), 10);
        $ip = substr($string, 0, $dcolonRPos);

        $o = new self($ip, $port);
        return $o;
    }

} 