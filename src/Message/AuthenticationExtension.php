<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Chris
 * Date: 16-6-13
 * Time: 23:14
 * To change this template use File | Settings | File Templates.
 */

namespace Devristo\TorrentTracker\Message;


class AuthenticationExtension {
    protected $username;
    protected $salt;
    protected $passwordHash;

    /**
     * @return mixed
     */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    public static function fromBytes($data, &$offset){
        $hashPositionMarker = $offset;

        list($length) = array_values(unpack("n", substr($data, $offset, 2)));
        $offset += 2;

        $username = substr($data, $offset, $length);
        $offset += $length;

        $hash = substr($data, $offset, 8);
        $offset += 8;

        $o = new self();

        $o->username = $username;
        $o->passwordHash = $hash;
        $o->salt = sha1(substr($data, 0, $hashPositionMarker), true);

        $offset += $length;

        return $o;
    }
}