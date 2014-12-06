<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:07
 */

namespace Devristo\TorrentTracker;

use Devristo\TorrentTracker\Exceptions\TrackerException;
use Devristo\TorrentTracker\Model\AnnounceDifference;
use Devristo\TorrentTracker\Model\SwarmPeer;
use Devristo\TorrentTracker\Repository\TorrentRepositoryInterface;
use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Message\AnnounceResponse;
use Evenement\EventEmitter;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;

class Tracker extends EventEmitter{
    protected $maxAnnouncePeers;
    protected $invalidationFactor = 3;
    protected $announceInterval = 60;
    protected $lastInvalidate = 0;

    /**
     * @return mixed
     */
    public function getMaxAnnouncePeers()
    {
        return $this->maxAnnouncePeers;
    }

    /**
     * @param mixed $maxAnnouncePeers
     */
    public function setMaxAnnouncePeers($maxAnnouncePeers)
    {
        $this->maxAnnouncePeers = $maxAnnouncePeers;
    }

    public function __construct(TorrentRepositoryInterface $repository){
        $this->torrentRepository = $repository;
        $this->invalidateSessions();
    }

    public function invalidateSessions(){
        $sessionTimeout = $this->invalidationFactor * $this->announceInterval;

        if($this->lastInvalidate + $sessionTimeout < time()) {
            $time = time() - $sessionTimeout;
            $this->torrentRepository->invalidateSessionsByTime($time);

            $this->lastInvalidate = time();
        }
    }

    public function announce(AnnounceRequest $trackerRequest){
        $this->invalidateSessions();

        $infoHash = $trackerRequest->getInfoHash();

        $diff = $this->getAnnounceDiff($trackerRequest);

        $eventObject = new TrackerEvent();
        $this->emit("announce", array($eventObject, $trackerRequest, $diff));
        if ($eventObject->isCanceled()) {
            throw new TrackerException($trackerRequest, $eventObject->getError());
        }

        try {
            $this->torrentRepository->updatePeer(
                $trackerRequest->getInfoHash(),
                $trackerRequest->getPeerId(),
                $trackerRequest->getKey(),
                $trackerRequest
            );

            $peers = $this->torrentRepository->getPeers($infoHash);

            $peersWithoutSelf = array_filter($peers, function(AnnounceRequest $peer) use ($trackerRequest){
                return !(
                    $peer->getPeerId() == $trackerRequest->getPeerId()
                    && $peer->getKey() == $trackerRequest->getKey()
                );
            });

            $seeders = array_filter($peersWithoutSelf, function(AnnounceRequest $peer){return $peer->getLeft() == 0;});
            $leechers = array_filter($peersWithoutSelf, function(AnnounceRequest $peer){return $peer->getLeft() != 0;});

            $numSeeders = count($seeders);
            $numLeechers = count($leechers);

            // For leechers we give leechers + seeders back
            if ($trackerRequest->getLeft() > 0) {
                $peers = array_merge($seeders, $leechers);
                shuffle($peers);
            } else {
                // Prioritise leechers over peers when you are a seeder
                shuffle($leechers);
                shuffle($seeders);
                $peers = array_merge($leechers, $seeders);
            }

            // Return a subset of peers
            $numPeers = min(min(count($peers), $trackerRequest->getNumWant()), $this->getMaxAnnouncePeers());
            $peers = array_slice($peers, 0, $numPeers);

            $peers = array_map(function(AnnounceRequest $request){
                return new SwarmPeer($request->getIpv4(), $request->getPort());
            }, $peers);

            $response = new AnnounceResponse($trackerRequest);
            $response->setLeechers($numLeechers);
            $response->setSeeders($numSeeders);
            $response->setPeers($peers);

            return $response;
        } catch (\Exception $e) {
            throw new TrackerException($trackerRequest, "Error =( contact crew please");
        }
    }

    /**
     * @param AnnounceRequest $trackerRequest
     * @return AnnounceDifference|static
     */
    protected function getAnnounceDiff(AnnounceRequest $trackerRequest)
    {
        $previousAnnounce = $this->torrentRepository->getPeer(
            $trackerRequest->getInfoHash(),
            $trackerRequest->getPeerId(),
            $trackerRequest->getKey()
        );

        $diff = $previousAnnounce
            ? AnnounceDifference::diff($previousAnnounce, $trackerRequest)
            : new AnnounceDifference();
        return $diff;
    }
} 