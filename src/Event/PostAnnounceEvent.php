<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 7-12-2014
 * Time: 12:34
 */

namespace Devristo\TorrentTracker\Event;


use Devristo\TorrentTracker\Message\AnnounceRequestInterface;
use Devristo\TorrentTracker\Message\AnnounceResponse;
use Devristo\TorrentTracker\Model\AnnounceDifference;

class PostAnnounceEvent extends AnnounceEvent {
    protected $response;

    public function __construct(AnnounceRequestInterface $request, AnnounceDifference $difference, AnnounceResponse $response){
        parent::__construct($request, $difference);
        $this->response = $response;
    }

    /**
     * @return AnnounceResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param AnnounceResponse $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}