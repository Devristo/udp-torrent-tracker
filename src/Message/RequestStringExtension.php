<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 23:14
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;


class RequestStringExtension {
    protected $requestString;

    /**
     * @param mixed $requestString
     */
    public function setRequestString($requestString)
    {
        $this->requestString = $requestString;
    }

    /**
     * @return mixed
     */
    public function getRequestString()
    {
        return $this->requestString;
    }

    public static function fromBytes($data, &$offset){
        list($length) = array_values(unpack("C", substr($data, $offset, 1)));
        $offset += 1;

        $o = new self();
        $o->requestString = substr($data, $offset, $length);
        $offset += $length;

        return $o;
    }
}