<?php
use Devristo\TorrentTracker\UserAgentIdentifier;

/**
 * Created by PhpStorm.
 * User: Chris
 * Date: 9-6-2014
 * Time: 10:55
 */

class UserAgentIdentifierTest extends PHPUnit_Framework_TestCase {
    public function test_azureus(){
        $o = new UserAgentIdentifier();

        $this->assertEquals(
            ['Azureus', '2.0.6.0'],
            $o->byPeerId('-AZ2060-')
        );

        $this->assertEquals(
            ['FlashGet', '0.1.8.0'],
            $o->byPeerId('-FG0180')
        );

        $this->assertEquals(
            ['µTorrent','2.2.1.0'],
            $o->byPeerId('-UT2210-')
        );

        $this->assertEquals(
            ['µTorrent for Mac','1.6.4.0'],
            $o->byPeerId('-UM1640-')
        );

        $this->assertEquals(
            ['Transmission','2.1.1.0'],
            $o->byPeerId('-TR2110-')
        );

        $this->assertEquals(
            ['Deluge','1.3.5.0'],
            $o->byPeerId('-DE1350-')
        );
    }

    public function test_shadows(){
        $o = new UserAgentIdentifier();

        $this->assertEquals(['Shadow\'s client', '5.8.11'], $o->byPeerId('S58B-----'));
    }

    public function test_mainline(){
        $o = new UserAgentIdentifier();

        $this->assertEquals(['Mainline', '4.3.6'], $o->byPeerId('M4-3-6--'));
        $this->assertEquals(['Mainline', '4.20.8'], $o->byPeerId('M4-20-8--'));
        $this->assertEquals(['Queen Bee', '1.0.0'], $o->byPeerId('Q1-0-0--'));
        $this->assertEquals(['Queen Bee', '1.10.0'], $o->byPeerId('Q1-10-0-'));
    }

    public function test_xbt(){
        $o = new UserAgentIdentifier();

        $this->assertEquals(['XBT', '0.5.4'], $o->byPeerId('XBT054d-'));
    }

    public function test_mldonkey(){
        $o = new UserAgentIdentifier();

        $this->assertEquals(['MLdonkey', '2.7.2'], $o->byPeerId('-ML2.7.2-kgjjfkd'));
    }
}
 