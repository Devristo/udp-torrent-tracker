udp-torrent-tracker
===================

```php
use Devristo\UdpTorrentTracker\Messages\AnnounceInput;


$server = new \Devristo\UdpTorrentTracker\Server();
$logger = new \Zend\Log\Logger();
$logger->addWriter(new Zend\Log\Writer\Stream("php://stderr"));

$server->getEventManager()->attach("announce", function(\Zend\EventManager\Event $event) use ($server, $logger){
    /**
     * @var $request AnnounceInput
     */
    $request = $event->getParam("announce");

    # Use your own code to find the torrent and the user    
    $torrent = getFromInfoHash($request->getInfoHash());
    $user = getUserFromPasskey($request->getRequestUri());
    
    # And your own code to check if the user may download
    if(!$user->mayDownload($torrent)){
        $event->stopPropagation();
        $server->sendError($request->getPeerId(), $request->getPeerPort(), $request->getTransactionId(), "May not download!");
        return;
    }
    
    # And your own code to store the statistics
    $torrent->registerPeer($user, array(
        "downloaded" => $request->getDownloaded(),
        "uploaded" => $request->getUploaded(),
        "left" => $request->getLeft(),
        "event" => $request->getEvent(),
        "info_hash" => $request->getInfoHash(),
        "peer_id" => $request->getPeerId(),
        "numwant" => $request->getNumWant(),
        "ip" => $request->getIpv4(),
        "port" => $request->getPort(),
        "user_agent" => ""
    ));


    # And to get a list of peers
    $peers = $torrent->getPeers($compact=0, $peerid=0, $request->getNumWant())
    
    # Finally you can use the API to send back the results
    $server->replyAnnounce($request, $torrent->seeders, $torrent->leechers, $peers);
});

$server->getEventManager()->attach("input", function(\Zend\EventManager\Event $event) use ($logger){
    $request = $event->getParam("packet");
    $logger->debug(sprintf("Got input packet with type %s", $request->getType()));
});

$server->getEventManager()->attach("listen-start", function(\Zend\EventManager\Event $event) use($logger, $server){
    $logger->info(sprintf("Listening at port %d", $server->getPort()));
});

$server->getEventManager()->attach("exception", function(\Zend\EventManager\Event $event) use($logger, $server){
    $logger->err($event->getParam("exception"));
});

$server->setIp("0.0.0.0");
$server->setPort(6881);
$server->run();
```
