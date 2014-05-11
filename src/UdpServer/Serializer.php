<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 8-5-2014
 * Time: 21:54
 */

namespace Devristo\TorrentTracker\UdpServer;


use Devristo\TorrentTracker\Exceptions\ProtocolViolationException;
use Devristo\TorrentTracker\Message\AnnounceResponse;
use Devristo\TorrentTracker\Message\AuthenticationExtension;
use Devristo\TorrentTracker\Message\TrackerResponse;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpConnectionResponse;
use Devristo\TorrentTracker\Message\ErrorResponse;
use Devristo\TorrentTracker\Message\Pack;
use Devristo\TorrentTracker\Message\TrackerRequest;
use Devristo\TorrentTracker\Message\RequestStringExtension;
use Devristo\TorrentTracker\Message\ScrapeResponse;
use Devristo\TorrentTracker\UdpServer\Message\UdpAnnounceRequest;
use Devristo\TorrentTracker\UdpServer\Message\UdpScrapeRequest;

class Serializer {
    const PACKET_TYPE_CONNECT = 0;
    const PACKET_TYPE_ANNOUNCE = 1;
    const PACKET_TYPE_SCRAPE = 2;

    /**
     * @param $data
     * @return TrackerRequest
     */
    public function decode($data){
        if (strlen($data) < 16)
            throw new ProtocolViolationException("Data packet should be at least 16 bytes long");

        $struct = unpack("Naction", substr($data, 8, 4));
        $action = $struct['action'];

        switch ($action) {
            case self::PACKET_TYPE_CONNECT:
                return $this->decodeConnect($data);
            case self::PACKET_TYPE_ANNOUNCE:
                return $this->decodeAnnounce($data);
            case self::PACKET_TYPE_SCRAPE:
                return $this->decodeScrape($data);
            default:
                throw new \InvalidArgumentException("Unrecognized action");
        }
    }

    public function decodeAnnounce($data){
        if(strlen($data) < 20)
            throw new ProtocolViolationException("Data packet should be at least 20 bytes long");

        $offset = 0;
        $connectionId = substr($data, $offset, 8);
        $offset += 8;

        $struct = unpack("Naction/NtransactionId", substr($data, $offset, 8));

        $action = $struct['action'];
        $transactionId = $struct['transactionId'];
        $offset += 8;

        $infoHash = substr($data, $offset, 20);
        $offset += 20;

        $peerId = substr($data, $offset, 20);
        $offset += 20;

        $downloaded = Pack::unpack_int64be(substr($data, $offset, 8));
        $offset += 8;

        $left = Pack::unpack_int64be(substr($data, $offset, 8));
        $offset += 8;

        $uploaded = Pack::unpack_int64be(substr($data, $offset, 8));
        $offset += 8;

        list(,$event) = Pack::unpack_int32be(substr($data, $offset, 4)); $offset += 4;
        list(,$ipv4) = unpack("N", substr($data, $offset, 4)); $offset += 4;
        $ipv4 = long2ip($ipv4);


        list(,$key) = unpack("N", substr($data, $offset, 4)); $offset += 4;
        $numWant = Pack::unpack_int32be(substr($data, $offset, 4)); $offset += 4;

        list($port) = array_values(unpack("n", substr($data, $offset, 2)));
        $offset += 2;

        $o = new UdpAnnounceRequest();

        $o->setConnectionId(bin2hex($connectionId));
        $o->setTransactionId($transactionId);

        $o->setInfoHash(($infoHash));
        $o->setPeerId($peerId);
        $o->setDownloaded($downloaded);
        $o->setLeft($left);
        $o->setUploaded($uploaded);
        $o->setEvent($event);
        $o->setIpv4($ipv4);
        $o->setkey($key);
        $o->setNumWant($numWant);
        $o->setPort($port);

        # We have extensions
        if($offset + 1 <= strlen($data)){
            list($extensions) = array_values(unpack("C", substr($data, $offset,1)));
            $offset += 1;

            if(($extensions & 1) == 1)
                $o->setCredentials(AuthenticationExtension::fromBytes($data, $offset));

            if(($extensions & 2) == 2)
                $o->setRequestString(RequestStringExtension::fromBytes($data, $offset)->getRequestString());
        }

        if($action !== 1)
            throw new ProtocolViolationException("Action should be 1 for a ANNOUNCE INPUT");

        return $o;
    }

    public function decodeConnect($data)
    {
        if(strlen($data) < 16)
            throw new ProtocolViolationException("Data packet should be at least 16 bytes long");

        $o = new UdpConnectionRequest();

        $offset = 0;
        $connectionId = substr($data, $offset, 8);
        $offset += 8;

        $struct = unpack("Naction/Ntransaction", substr($data, $offset, 8));

        $o->setConnectionId(bin2hex($connectionId));
        $o->setTransactionId($struct['transaction']);

        if($struct['action'] !== 0)
            throw new ProtocolViolationException("Action should be 0 for a CONNECT INPUT");

        if($connectionId !== hex2bin("0000041727101980"))
            throw new ProtocolViolationException("ConnectionId shoulde be 0x41727101980 for CONNECT INPUT");

        return $o;
    }

    public function decodeScrape($data){
        if(strlen($data) < 20)
            throw new ProtocolViolationException("Data packet should be at least 20 bytes long");


        $offset = 0;
        $connectionId = substr($data, $offset, 8);
        $offset += 8;

        list(,$action) = (unpack("N", substr($data, $offset, 4)));
        $offset += 4;
        $transactionId = substr($data, $offset, 4);
        $offset +=4;

        $o = new UdpScrapeRequest();

        $o->setConnectionId(bin2hex($connectionId));
        $o->setTransactionId(bin2hex($transactionId));

        $infoHashes = array();
        while($offset + 20 <= strlen($data)){
            $infoHashes[] = (substr($data, $offset, 20));
            $offset += 20;
        }

        $o->setInfoHashes($infoHashes);


        if($action !== 2)
            throw new ProtocolViolationException("Action should be 2 for a SCRAPE INPUT");

        return $o;
    }

    public function encode(TrackerResponse $response){
        $encoders = array(
            "announce" => array($this, 'encodeAnnounce'),
            "connect" => array($this, 'encodeConnect'),
            "scrape" => array($this, 'encodeScrape'),
            "error" => array($this, 'encodeError')
        );

        $callable = $encoders[$response->getMessageType()];

        return call_user_func($callable, $response);
    }

    public function encodeScrape(ScrapeResponse $response){
        $header = pack("NN", 2, $response->getRequest()->getTransactionId());

        // Take order of request, this is important!
        foreach($response->getRequest()->getInfoHashes() as $infohash){
            $stats = $response->getStats()[$infohash];

            $header .= pack("N", $stats['complete']);
            $header .= pack("N", $stats['downloaded']);
            $header .= pack("N", $stats['incomplete']);
        }
        return $header;
    }

    public function encodeConnect(UdpConnectionResponse $response){
        return pack("NN", 0, $response->getRequest()->getTransactionId()).hex2bin($response->getConnectionId());
    }

    public function encodeAnnounce(AnnounceResponse $response){
        $header = pack("NNNNN", 1, $response->getRequest()->getTransactionId(), $response->getInterval(), $response->getLeechers(), $response->getSeeders());

        $peerData = '';
        foreach($response->getPeers() as $peer){
            $peerData .= pack("N", ip2long($peer->getIp())).pack("n", $peer->getPort());
        }

        return $header.$peerData;
    }

    public function encodeError(ErrorResponse $response){
        return pack("NN", 3, $response->getRequest()->getTransactionId()).$response->getMessage();
    }
} 