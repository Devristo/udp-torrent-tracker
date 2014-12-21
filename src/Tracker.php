<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:07
 */

namespace Devristo\TorrentTracker;

use Devristo\TorrentTracker\Event\AnnounceEvent;
use Devristo\TorrentTracker\Event\PostAnnounceEvent;
use Devristo\TorrentTracker\Message\AnnounceRequestInterface;
use Devristo\TorrentTracker\Message\ErrorResponse;
use Devristo\TorrentTracker\Model\AnnounceDifference;
use Devristo\TorrentTracker\Model\SwarmPeer;
use Devristo\TorrentTracker\Repository\TorrentRepositoryInterface;
use Devristo\TorrentTracker\Message\AnnounceResponse;
use Evenement\EventEmitter;

class Tracker extends EventEmitter{
    protected $invalidationFactor = 3;
    protected $announceInterval = 60;
    protected $lastInvalidate = 0;
    protected $configuration;


    public function __construct(TorrentRepositoryInterface $repository, Configuration $config){
        $this->torrentRepository = $repository;
        $this->configuration = $config;
        $this->torrentRepository->invalidateSessionsByTime();
    }


    public function announce(AnnounceRequestInterface $trackerRequest){
        $this->torrentRepository->invalidateSessionsByTime();

        $infoHash = $trackerRequest->getInfoHash();

        $trackerRequest->setAnnounceTime(new \DateTime());
        $trackerRequest->setExpirationTime((new \DateTime())->add(new \DateInterval("PT1200S")));

        $diff = $this->getAnnounceDiff($trackerRequest);

        if($diff->getUploaded())
            $trackerRequest->setUploadSpeed($diff->getUploaded() / $diff->getSeconds());

        if($diff->getDownloaded())
            $trackerRequest->setUploadSpeed($diff->getDownloaded() / $diff->getSeconds());

        $eventObject = new AnnounceEvent($trackerRequest, $diff);
        $this->emit("preAnnounce", array($eventObject));
        if ($eventObject->isCanceled()) {
            return new ErrorResponse($trackerRequest, $eventObject->getCancellationReason());
        }

        try {
            $this->torrentRepository->updatePeer(
                $trackerRequest->getInfoHash(),
                $trackerRequest->getPeerId(),
                $trackerRequest
            );

            $peers = $this->torrentRepository->getPeers($infoHash);

            $peersWithoutSelf = array_filter($peers, function(AnnounceRequestInterface $peer) use ($trackerRequest){
                return !(
                    $peer->getPeerId() == $trackerRequest->getPeerId()
                    && $peer->getKey() == $trackerRequest->getKey()
                );
            });

            $seeders = array_filter($peersWithoutSelf, function(AnnounceRequestInterface $peer){return $peer->getLeft() == 0;});
            $leechers = array_filter($peersWithoutSelf, function(AnnounceRequestInterface $peer){return $peer->getLeft() != 0;});

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
            $numPeers = min(min(count($peers), $trackerRequest->getNumWant()), $this->configuration->getMaxAnnouncePeers());
            $peers = array_slice($peers, 0, $numPeers);

            $peers = array_map(function(AnnounceRequestInterface $request){
                return new SwarmPeer($request->getIpv4(), $request->getPort());
            }, $peers);

            $response = new AnnounceResponse($trackerRequest);
            $response->setLeechers($numLeechers);
            $response->setSeeders($numSeeders);
            $response->setPeers($peers);

            $postAnnounceEvent = new PostAnnounceEvent($trackerRequest, $diff, $response);
            $this->emit("postAnnounce", array($postAnnounceEvent));

            if(!$postAnnounceEvent->isCanceled())
                return $response;
            else return new ErrorResponse($trackerRequest, $postAnnounceEvent->getCancellationReason());

        } catch (\Exception $e) {
            error_log($e);
            return new ErrorResponse($trackerRequest, "Error =( contact crew please");
        }
    }

    /**
     * @param AnnounceRequestInterface $trackerRequest
     * @return AnnounceDifference|static
     */
    protected function getAnnounceDiff(AnnounceRequestInterface $trackerRequest)
    {
        $previousAnnounce = $this->torrentRepository->getPeer(
            $trackerRequest->getInfoHash(),
            $trackerRequest->getPeerId()
        );

        $diff = $previousAnnounce
            ? AnnounceDifference::diff($previousAnnounce, $trackerRequest)
            : new AnnounceDifference();
        return $diff;
    }
} 