<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:09
 */

namespace Devristo\TorrentTracker\Model;

use Devristo\TorrentTracker\Message\AnnounceRequest;

interface TorrentRepositoryInterface {
    public function getByInfoHash($infoHash);

    /**
     * @param $infoHash
     * @return \Devristo\TorrentTracker\Model\SwarmPeer[]
     */
    public function getSeeders($infoHash);

    /**
     * @param $infoHash
     * @return \Devristo\TorrentTracker\Model\SwarmPeer[]
     */
    public function getLeechers($infoHash);

    public function startPeer(AnnounceRequest $input);

    public function updatePeer(AnnounceRequest $input);

    public function completePeer(AnnounceRequest $input);

    public function stopPeer(AnnounceRequest $input);

    public function trackTorrent(TorrentInterface $torrent);
}