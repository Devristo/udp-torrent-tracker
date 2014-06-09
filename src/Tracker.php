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
use React\Promise\Deferred;

class Tracker extends EventEmitter{
    protected $maxAnnouncePeers;
    protected $logger;

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

    public function __construct(LoggerInterface $logger, $repository){
        $this->torrentRepository = $repository;
        $this->logger = $logger;
    }

    public function bind(ServerInterface $server){
        $server->on("announce", array($this, 'announce'));
    }

    public function announce(ServerInterface $server, AnnounceRequest $trackerRequest, Deferred $result){
        $infoHash = $trackerRequest->getInfoHash();

        try{
            $previousAnnounce = $this->torrentRepository->getPeer(
                $trackerRequest->getInfoHash(),
                $trackerRequest->getPeerId(),
                $trackerRequest->getKey()
            );

            if($previousAnnounce)
                $this->logger->warning("Has previous announce!");

            $diff = $previousAnnounce
                ? AnnounceDifference::diff($previousAnnounce, $trackerRequest)
                : new AnnounceDifference();
        } catch (\Exception $e){
            $this->logger->error("Error looking up previous announce in repository", array(
                'exception' => $e
            ));
            return false;
        }

        $eventObject = new TrackerEvent();
        $this->emit("announce", array($eventObject, $trackerRequest, $diff));


        if ($eventObject->isCanceled()) {
            $result->reject(new TrackerException($trackerRequest, $eventObject->getError()));
            return false;
        }

        try {
            $this->torrentRepository->updatePeer(
                $trackerRequest->getInfoHash(),
                $trackerRequest->getPeerId(),
                $trackerRequest->getKey(),
                $trackerRequest
            );

            $peers = $this->torrentRepository->getPeers($infoHash);

            $seeders = array_filter($peers, function(AnnounceRequest $peer){return $peer->getLeft() == 0;});
            $leechers = array_filter($peers, function(AnnounceRequest $peer){return $peer->getLeft() != 0;});

            $numSeeders = count($seeders);
            $numLeechers = count($leechers);

            // Make sure the client does not get his own IP back
            $compositeKey = $trackerRequest->getPeerId().$trackerRequest->getKey();
            if(array_key_exists($compositeKey, $seeders))
                unset($seeders[$compositeKey]);

            if(array_key_exists($compositeKey, $leechers))
                unset($leechers[$compositeKey]);

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

            $result->resolve($response);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Cannot announce", array(
                'exception' => $e
            ));

            $result->reject(new TrackerException($trackerRequest, "Error =( contact crew please"));
            return false;
        }
    }
} 