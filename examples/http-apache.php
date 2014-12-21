<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 10-5-2014
 * Time: 18:35
 */

require_once __DIR__.'/../vendor/autoload.php';

use Devristo\TorrentTracker\Message\AnnounceRequest;
use Devristo\TorrentTracker\Repository\ArrayRepository;
use Devristo\TorrentTracker\HttpServer\Serializer;
use Devristo\TorrentTracker\Tracker;
use Guzzle\Http\Message\Request;

$repository = new ArrayRepository();

$tracker = new Tracker($repository);
$httpRequest = new Request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], apache_request_headers());
$serializer = new Serializer();

$trackerRequest = $serializer->parseRequest($httpRequest);
if($trackerRequest instanceof AnnounceRequest)
    $response = $tracker->announce($trackerRequest);
else
    throw new InvalidArgumentException();

echo $serializer->encode($response);