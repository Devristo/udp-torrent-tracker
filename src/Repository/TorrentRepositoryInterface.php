<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:09
 */

namespace Devristo\TorrentTracker\Repository;

use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\AnnounceRequestInterface;

interface TorrentRepositoryInterface {
    /**
     * @param $infoHash
     * @return \Devristo\TorrentTracker\Model\SwarmPeer[]
     */
    public function getPeers($infoHash);

    public function updatePeer($infoHash, $peerId, AnnounceRequestInterface $request);

    /**
     * @param $infoHash
     * @param $peerId
     * @param $key
     * @return AnnounceRequest|null
     */
    public function getPeer($infoHash, $peerId);

    public function invalidateSessionsByTime();
}