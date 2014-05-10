<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 13:47
 */

namespace Devristo\TorrentTracker\Model;


use Devristo\TorrentTracker\Messages\AnnounceRequest;

class ArrayRepository implements TorrentRepositoryInterface{
    /**
     * @var TorrentInterface[]
     */
    protected $torrents = array();

    /**
     * Set of Infohashes
     *
     * @var array
     */
    protected $peers = array();

    /**
     * Map of Infohash => [PeerID => seeder (bool)]
     * @var array
     */
    protected $seeders = array();

    /**
     * Map of Infohash => [PeerID => unixtimestamp]
     * @var array
     */
    protected $times = array();

    public function getByInfoHash($infoHash)
    {
        if(array_key_exists($infoHash, $this->torrents))
            return $this->torrents[$infoHash];
        else return null;
    }

    private function getPeers($infohash){
        if(!array_key_exists($infohash, $this->peers))
            $this->peers[$infohash] = array();

        return $this->peers[$infohash];
    }


    public function getSeeders($infoHash)
    {
        $peers = $this->getPeers($infoHash);

        return array_filter($peers, function(AnnounceRequest $peer){
            return $peer->getLeft() == 0;
        });
    }

    public function getLeechers($infoHash)
    {
        $peers = $this->getPeers($infoHash);

        return array_filter($peers, function(AnnounceRequest $peer){
            return $peer->getLeft() > 0;
        });
    }

    public function updatePeer(AnnounceRequest $input)
    {
        $peerId = $input->getPeerId();
        $infoHash = $input->getInfoHash();

        if($input->getEvent() == AnnounceRequest::EVENT_STOPPED){
            unset($this->peers[$infoHash][$peerId]);
            unset($this->times[$infoHash][$peerId]);
        } else {
            $this->peers[$infoHash][$peerId] = $input;
            $this->times[$infoHash][$peerId] = time();
        }
    }

    public function trackTorrent(TorrentInterface $torrent){
        $this->torrents[$torrent->getInfoHash()] = $torrent;
    }
}