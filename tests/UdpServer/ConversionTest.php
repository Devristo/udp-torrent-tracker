<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/27/14
 * Time: 4:04 PM
 */

namespace Devristo\TorrentTracker\UdpServer;

use Devristo\TorrentTracker\Messages\AnnounceRequest;
use Devristo\TorrentTracker\Messages\AnnounceResponse;
use Devristo\TorrentTracker\Messages\ConnectionRequest;
use Devristo\TorrentTracker\Messages\ConnectionResponse;
use Devristo\TorrentTracker\Model\SwarmPeer;

class ConversionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Serializer
     */
    protected $conversion;

    public function setUp()
    {
        $this->conversion = new Serializer();
    }

    /**
     * Simulates an ANNOUNCE scenario using data captured during a session between libTorrent and a third party open
     * UDP-tracker
     */
    public function test_announce()
    {
        # Opening CONNECT handshake sent by CLIENT
        $connect_raw = hex2bin("0000041727101980000000006b61e6f3");
        $connect = $this->conversion->decode($connect_raw);
        $this->assertInstanceOf(ConnectionRequest::class, $connect);

        # Response by SERVER
        $connect_response = new ConnectionResponse($connect);
        $connect_response->setConnectionId("e65e7e630f50b38a");
        $connect_response->setTransactionId($connect->getTransactionId());

        # Check whether this response is equal to the captured data
        $this->assertEquals("000000006b61e6f3e65e7e630f50b38a", bin2hex($this->conversion->encode($connect_response)));

        # ANNOUNCE sent by CLIENT
        $announce_raw = hex2bin("e65e7e630f50b38a00000001be4420316a36de201df2f1b2c817474c3075ff0eaa8c77852d7142333042302d4371463573297537796b36280000000000000000000000002a00000000000000000000000000000200000000a0ad9d43000000c81ae102092f616e6e6f756e6365");
        $announce = $this->conversion->decode($announce_raw);

        $this->assertInstanceOf(AnnounceRequest::class, $announce);

        # Test whether we parsed the announce correctly
        $this->assertEquals("6a36de201df2f1b2c817474c3075ff0eaa8c7785", bin2hex($announce->getInfoHash()));
        $this->assertEquals("e65e7e630f50b38a", $announce->getConnectionId());

        # Recreate the ANNOUNCE response

        $our_response = new AnnounceResponse($announce);
        $our_response->setSeeders(6);
        $our_response->setLeechers(1);
        $our_response->setInterval(1804);

        $peers = [["145.94.47.19", 6881], ["178.237.32.132", 1500], ["177.159.212.232", 26591], ["177.37.160.140", 18185], ["109.227.63.38", 6881], ["99.166.170.120", 6881], ["76.0.151.55", 27303]];

        $our_response->setPeers(array_map(function ($peer){
            return new SwarmPeer($peer[0], $peer[1]);
        }, $peers));


        # Check whether our responds matches the captured data
        $announce_response_raw = hex2bin("00000001be4420310000070c0000000100000006915e2f131ae1b2ed208405dcb19fd4e867dfb125a08c47096de33f261ae163a6aa781ae14c0097376aa7");
        $this->assertEquals($announce_response_raw, $this->conversion->encode($our_response));
    }
}
 