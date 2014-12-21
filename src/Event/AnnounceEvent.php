<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 7-12-2014
 * Time: 12:01
 */

namespace Devristo\TorrentTracker\Event;


use Devristo\TorrentTracker\Message\AnnounceRequestInterface;
use Devristo\TorrentTracker\Model\AnnounceDifference;

class AnnounceEvent extends TrackerEvent{
    protected $request;
    protected $difference;

    public function __construct(AnnounceRequestInterface $request, AnnounceDifference $difference){
        $this->request = $request;
        $this->difference = $difference;
    }

    /**
     * @return AnnounceRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param AnnounceRequestInterface $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return AnnounceDifference
     */
    public function getDifference()
    {
        return $this->difference;
    }

    /**
     * @param AnnounceDifference $difference
     */
    public function setDifference($difference)
    {
        $this->difference = $difference;
    }
}