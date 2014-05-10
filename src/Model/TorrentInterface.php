<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:09
 */

namespace Devristo\TorrentTracker\Model;

interface TorrentInterface {
    public function getInfoHash();
    public function getFileSize();
} 