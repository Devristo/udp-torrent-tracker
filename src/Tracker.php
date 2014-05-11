<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:07
 */

namespace Devristo\TorrentTracker;

use Devristo\TorrentTracker\Exceptions\TrackerException;
use Devristo\TorrentTracker\Model\SwarmPeer;
use Devristo\TorrentTracker\Model\TorrentRepositoryInterface;
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

    public function __construct(LoggerInterface $logger, TorrentRepositoryInterface $repository){
        $this->torrentRepository = $repository;
        $this->logger = $logger;

        $this->onAnnounce = $this->createAnnounceHandler();
    }

    public function bind(ServerInterface $server){
        $server->on("announce", $this->onAnnounce);
    }

    /**
     * @return callable
     */
    protected function createAnnounceHandler()
    {
        return function (ServerInterface $server, AnnounceRequest $trackerRequest, Deferred $result) {
            $eventObject = new TrackerEvent($trackerRequest);

            $infoHash = $trackerRequest->getInfoHash();

            try {
                $this->emit("announce", array($eventObject));
            } catch (\Exception $e) {
                $this->logger->error("Exception raised in announce event listener", array(
                    'exception' => $e
                ));

                $result->reject(new TrackerException($trackerRequest, "Error =( contact crew please"));
                return false;
            }

            if ($eventObject->isCanceled()) {
                $result->reject(new TrackerException($trackerRequest, $eventObject->getError()));
                return false;
            }

            try {
                $event = $trackerRequest->getEvent();
                $this->torrentRepository->updatePeer($trackerRequest);

                $seeders = $this->torrentRepository->getSeeders($infoHash);
                $leechers = $this->torrentRepository->getLeechers($infoHash);

                $numSeeders = count($seeders);
                $numLeechers = count($leechers);

                if(array_key_exists($trackerRequest->getPeerId(), $seeders))
                    unset($seeders[$trackerRequest->getPeerId()]);

                if(array_key_exists($trackerRequest->getPeerId(), $leechers))
                    unset($leechers[$trackerRequest->getPeerId()]);

                // For leechers we give leechers + seeders back
                if ($event == AnnounceRequest::EVENT_NONE || $event == AnnounceRequest::EVENT_STARTED) {
                    $peers = array_merge($seeders, $leechers);
                    shuffle($peers);
                } else {
                    // Prioritise leechers over peers when you are a seeder
                    shuffle($leechers);
                    shuffle($peers);
                    $peers = array_merge($leechers, $seeders);
                }

                $numPeers = min(min(count($peers), $trackerRequest->getNumWant()), $this->maxAnnouncePeers);
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
        };
    }
} 