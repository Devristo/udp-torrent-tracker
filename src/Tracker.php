<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:07
 */

namespace Devristo\TorrentTracker;

use Devristo\TorrentTracker\Model\SwarmPeer;
use Devristo\TorrentTracker\Model\TorrentRepositoryInterface;
use Devristo\TorrentTracker\Messages\AnnounceRequest;
use Devristo\TorrentTracker\Messages\AnnounceResponse;
use Devristo\TorrentTracker\Messages\ErrorResponse;
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
        return function (ServerInterface $server, AnnounceRequest $input, Deferred $result) {
            $response = new Session();

            $infoHash = $input->getInfoHash();

            try {
                $this->emit("announce", [
                    "request" => $input,
                    "response" => $response
                ]);
            } catch (\Exception $e) {
                $this->logger->error("Exception raised in announce event listener", array(
                    'exception' => $e
                ));

                $result->reject(new ErrorResponse($input, "Error =( contact crew please"));
                return false;
            }

            if ($response->isCanceled()) {
                $result->reject(new ErrorResponse($input, $response->getError()));
                return false;
            }

            try {
                $event = $input->getEvent();
                $this->torrentRepository->updatePeer($input);

                $seeders = $this->torrentRepository->getSeeders($infoHash);
                $leechers = $this->torrentRepository->getLeechers($infoHash);

                $numSeeders = count($seeders);
                $numLeechers = count($leechers);

                if(array_key_exists($input->getPeerId(), $seeders))
                    unset($seeders[$input->getPeerId()]);

                if(array_key_exists($input->getPeerId(), $leechers))
                    unset($leechers[$input->getPeerId()]);

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

                $numPeers = min(min(count($peers), $input->getNumWant()), $this->maxAnnouncePeers);
                $peers = array_slice($peers, 0, $numPeers);

                $peers = array_map(function(AnnounceRequest $request){
                    return new SwarmPeer($request->getIpv4(), $request->getPort());
                }, $peers);

                $response = new AnnounceResponse($input);
                $response->setLeechers($numLeechers);
                $response->setSeeders($numSeeders);
                $response->setPeers($peers);

                $result->resolve($response);

                return true;
            } catch (\Exception $e) {
                $this->logger->error("Cannot announce", array(
                    'exception' => $e
                ));

                $result->reject(new ErrorResponse($input, "Error =( contact crew please"));
                return false;
            }
        };
    }
} 