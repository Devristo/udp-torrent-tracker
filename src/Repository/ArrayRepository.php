<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10-5-14
 * Time: 13:47
 */

namespace Devristo\TorrentTracker\Repository;


use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Model\TorrentInterface;

class ArrayRepository implements TorrentRepositoryInterface
{
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

    public function getPeers($infohash)
    {
        if (!array_key_exists($infohash, $this->peers))
            $this->peers[$infohash] = array();

        return $this->peers[$infohash];
    }

    public function updatePeer($infoHash, $peerId, $key, AnnounceRequest $request)
    {
        $compositeKey = $peerId.$key;

        if ($request->getEvent() == AnnounceRequest::EVENT_STOPPED) {
            unset($this->peers[$infoHash][$compositeKey]);
            unset($this->times[$infoHash][$compositeKey]);
        } else {
            $this->peers[$infoHash][$compositeKey] = $request;
            $this->times[$infoHash][$compositeKey] = time();
        }
    }

    /**
     * @param $infoHash
     * @param $peerId
     * @param $key
     * @return AnnounceRequest|null
     */
    public function getPeer($infoHash, $peerId, $key)
    {
        $compositeKey = $peerId.$key;

        if(array_key_exists($infoHash, $this->peers) && array_key_exists($compositeKey, $this->peers[$infoHash]))
            return $this->peers[$infoHash][$compositeKey];
        else return null;
    }
}