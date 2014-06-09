<?php
/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 9-6-2014
 * Time: 10:23
 */

namespace Devristo\TorrentTracker;


class UserAgentIdentifier {
    private static $shadows = "#^[a-zA-Z][a-zA-Z0-9\\.\\-]{5}#";
    private static $mainline = "#^[a-zA-Z][0-9\\-]{5}#";
    private static $azureus = "#^-[a-zA-Z~]{2}[0-9]{4}-?#";
    private static $xbt = "#^XBT[0-9]{3}(d|-)-#";
    private static $mldonkey = "#^-ML(?<version>[0-9\\.]+)-#";

    public function byPeerId($peerId){
        $canBeAzureus = preg_match(self::$azureus, $peerId);
        $canBeShadows = preg_match(self::$shadows, $peerId);
        $canBeMainline = preg_match(self::$mainline, $peerId);
        $canBeXBT = preg_match(self::$xbt, $peerId);
        $canBeMLdonkey = preg_match(self::$mldonkey, $peerId);

        if($canBeAzureus)
            return $this->byAzureusStyle($peerId);
        elseif($canBeXBT)
            return $this->byXBT($peerId);
        elseif($canBeMLdonkey)
            return $this->byMLdonkey($peerId);
        elseif($canBeMainline)
            return $this->byMainLine($peerId);
        elseif($canBeShadows)
            return $this->byShadowStyle($peerId);

        else return null;
    }

    private function byXBT($peerId){
        $version = implode(".", str_split(substr($peerId, 3, 3)));
        return ['XBT', $version];
    }

    private function byMainLine($peerId){
        $clients = array(
            'M' => 'Mainline',
            'Q' => 'Queen Bee'
        );

        if(!array_key_exists($peerId[0], $clients))
            return null;

        $client = $clients[$peerId[0]];

        $i = 0;
        do{
            $i++;
        } while($i < strlen($peerId) && (ctype_digit($peerId[$i]) || $peerId[$i] == '-'));

        $version = implode(".",preg_split('#-#', substr($peerId,1,$i), -1, PREG_SPLIT_NO_EMPTY));

        return array($client, $version);
    }

    private function byAzureusStyle($peerId){
        $clients = array(
            '7T' => 'aTorrent for Android',
            'AB' => 'AnyEvent::BitTorrent',
            'AG' => 'Ares',
            'A~' => 'Ares',
            'AR' => 'Arctic',
            'AV' => 'Avicora',
            'AT' => 'Artemis',
            'AX' => 'BitPump',
            'AZ' => 'Azureus',
            'BB' => 'BitBuddy',
            'BC' => 'BitComet',
            'BF' => 'Bitflu',
            'BG' => 'BTG (uses Rasterbar libtorrent)',
            'BL' => 'BitBlinder',
            'BP' => 'BitTorrent Pro (Azureus + spyware)',
            'BR' => 'BitRocket',
            'BS' => 'BTSlave',
            'BT' => 'BBtor',
            'BW' => 'BitWombat',
            'BX' => '~Bittorrent X',
            'CD' => 'Enhanced CTorrent',
            'CT' => 'CTorrent',
            'DE' => 'Deluge',
            'DP' => 'Propagate Data Client',
            'EB' => 'EBit',
            'ES' => 'electric sheep',
            'FC' => 'FileCroc',
            'FG' => 'FlashGet',
            'FT' => 'FoxTorrent',
            'FX' => 'Freebox BitTorrent',
            'GS' => 'GSTorrent',
            'HK' => 'Hekate',
            'HL' => 'Halite',
            'HM' => 'hMule (uses Rasterbar libtorrent)',
            'HN' => 'Hydranode',
            'IL' => 'iLivid',
            'JS' => 'Justseed.it client',
            'JT' => 'JavaTorrent',
            'KG' => 'KGet',
            'KT' => 'KTorrent',
            'LC' => 'LeechCraft',
            'LH' => 'LH-ABC',
            'LP' => 'Lphant',
            'LT' => 'libtorrent',
            'lt' => 'libTorrent',
            'LW' => 'LimeWire',
            'MK' => 'Meerkat',
            'MO' => 'MonoTorrent',
            'MP' => 'MooPolice',
            'MR' => 'Miro',
            'MT' => 'MoonlightTorrent',
            'NB' => 'Net::BitTorrent',
            'NX' => 'Net Transport',
            'OS' => 'OneSwarm',
            'OT' => 'OmegaTorrent',
            'PB' => 'Protocol::BitTorrent',
            'PD' => 'Pando',
            'PT' => 'PHPTracker',
            'qB' => 'qBittorrent',
            'QD' => 'QQDownload',
            'QT' => 'Qt 4 Torrent example',
            'RT' => 'Retriever',
            'RZ' => 'RezTorrent',
            'S~' => 'Shareaza alpha/beta',
            'SB' => '~Swiftbit',
            'SD' => 'Thunder (aka XùnLéi)',
            'SM' => 'SoMud',
            'SP' => 'BitSpirit',
            'SS' => 'SwarmScope',
            'ST' => 'SymTorrent',
            'st' => 'sharktorrent',
            'SZ' => 'Shareaza',
            'TE' => 'terasaur Seed Bank',
            'TL' => 'Tribler',
            'TN' => 'TorrentDotNET',
            'TR' => 'Transmission',
            'TS' => 'Torrentstorm',
            'TT' => 'TuoTu',
            'UL' => 'uLeecher!',
            'UM' => 'µTorrent for Mac',
            'UT' => 'µTorrent',
            'VG' => 'Vagaa',
            'WT' => 'BitLet',
            'WY' => 'FireTorrent',
            'XL' => 'Xunlei',
            'XS' => 'XSwifter',
            'XT' => 'XanTorrent',
            'XX' => 'Xtorrent',
            'ZT' => 'ZipTorrent'
        );

        $key = substr($peerId, 1, 2);

        if(!array_key_exists($key, $clients))
            return null;

        $client = $clients[$key];
        $version = implode(".", str_split(substr($peerId, 3, 4)));

        return array($client, $version);
    }

    private function byShadowStyle($peerId){
        $clients = array(
            'A' => 'ABC',
            'O' => 'Osprey Permaseed',
            'Q' => 'BTQueue',
            'R' => 'Tribler',
            'S' => 'Shadow\'s client',
            'T' => 'BitTornado',
            'U' => 'UPnP NAT Bit Torrent'
        );

        if(!array_key_exists($peerId[0], $clients))
            return null;

        $lang = array_flip(str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.-"));
        $version = array();

        for($i=1; $i<6 && $peerId[$i] != '-'; $i++){
            $version[] = $lang[$peerId[$i]];
        }

        $client = $clients[$peerId[0]];

        return array($client, implode(".", $version));
    }

    private function byMLdonkey($peerId)
    {
        preg_match(self::$mldonkey, $peerId, $match);

        if(!array_key_exists('version', $match) || !$match['version'])
            return null;

        return array('MLdonkey', $match['version']);
    }
} 