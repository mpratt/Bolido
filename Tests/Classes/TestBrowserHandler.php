<?php
/**
 * TestBrowserHandler.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

if (!defined('BOLIDO'))
    define('BOLIDO', 'TestBrowserHandler');

require_once(dirname(__FILE__) . '/../../Bolido/Sources/BrowserHandler.class.php');

class TestBrowserHandler extends PHPUnit_Framework_TestCase
{
    protected $browser;

    /**
     * Setup the test environment
     */
    public function setUp() { $this->browser = new BrowserHandler(); }

    /**
     * Tests that the BrowserHandler detects mobile browsers
     */
    public function testMobileDetection()
    {
        $userAgents = array(
                            'Mozilla/5.0 (iPod; U; CPU iPhone OS 4_3_3 like Mac OS X; ja-jp) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8J2 Safari/6533.18.5',
                            'Mozilla/5.0 (iPhone; U; fr; CPU iPhone OS 4_2_1 like Mac OS X; fr) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148a Safari/6533.18.5',
                            'BlackBerry8110/4.3.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/118',
                            'Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
                            'Mozilla/5.0 (Linux; U; Android 2.2.1; de-de; LG-P350 Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1 MMS/LG-Android-MMS-V1.0/1.2',
                            'Mozilla/5.0 (Linux; U; Android 3.0.1; de-de; MZ601 Build/H.6.1-38-5) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13',
                            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Tablet PC 2.0; MAAR; .NET4.0C)',
                            'Mozilla/5.0 (iPad; U; CPU OS 4_3 like Mac OS X; de-de) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8F191 Safari/6533.18.5',
                            'Mozilla/5.0 (X11; U; Linux armv61; en-US; rv:1.9.1b2pre) Gecko/20081015 Fennec/1.0a1',
                            'Mozilla/5.0 (SymbianOS/9.1; U; en-us) AppleWebKit/413 (KHTML, like Gecko) Safari/413',
                            'Palm680/RC1 Mozilla/4.0 (compatible; MSIE 6.0; Windows 98; PalmSource/Palm-D053; Blazer/4.5) 16;320x320 UP.Link/6.3.1.17.06.3.1.17.0',
                            'Mozilla/4.0 (compatible; MSIE 6.0; Windows 98; PalmSource/Palm-D050; Blazer/4.3) 16;320x320)',
                            'SAMSUNG-SGH-D900/1.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 UP.Browser/6.2.3.3.c.1.101 (GUI) MMP/2.0 UP.Link/6.3.1.12.0',
                            'SonyEricssonT280i/R1CB002 TelecaBrowser/Q04C1-1 Profile/MIDP-2.0 Configuration/CLDC-1.1',
                            'Mozilla/5.0 (ZTE-E_N72/N72V1.0.0B02;U;Windows Mobile/6.1;Profile/MIDP-2.0 Configuration/CLDC-1.1;320*240;CTC/2.0) IE/6.0 (compatible; MSIE 4.01; Windows CE; PPC)/UC Browser7.7.1.88',
                            'Mozilla/4.0 (compatible;MSIE 6.0;Windows95;PalmSource) Netfront/3.0;8;320x320',
                           );

        foreach ($userAgents as $agent)
        {
            // echo $agent . PHP_EOL;
            $this->browser->loadUserAgent($agent);
            $this->assertTrue($this->browser->isMobile());
        }
    }

    /**
     * Tests that the BrowserHandler detects operating systems
     */
    public function testOsDetection()
    {
        $this->browser->loadUserAgent('Mozilla/5.0 (Linux; U; Android 3.0.1; de-de; MZ601 Build/H.6.1-38-5) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13');
        $this->assertEquals($this->browser->getOS(), 'android');

        $this->browser->loadUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; Media Center PC 6.0; InfoPath.2; MS-RTC LM 8)');
        $this->assertEquals($this->browser->getOS(), 'win');

        $this->browser->loadUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_2) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.215 Safari/535.1');
        $this->assertEquals($this->browser->getOS(), 'mac');

        $this->browser->loadUserAgent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.220 Safari/535.1');
        $this->assertEquals($this->browser->getOS(), 'linux');

        $this->browser->loadUserAgent('Mozilla/5.0 (X11; U; FreeBSD x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.204 Safari/534.16');
        $this->assertEquals($this->browser->getOS(), 'freebsd');

        $this->browser->loadUserAgent('Mozilla/5.0 (X11; U; OpenBSD i386; en-US; rv:1.7.13) Gecko/20060901');
        $this->assertEquals($this->browser->getOS(), 'openbsd');

        $this->browser->loadUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/532.2 (KHTML, like Gecko) Chrome/4.0.221.6 Safari/532.2');
        $this->assertEquals($this->browser->getOS(), 'win');

        $this->browser->loadUserAgent('Mozilla/5.0 (Windows; U; Windows NT 6.1; x64; fr; rv:1.9.2.13) Gecko/20101203 Firebird/3.6.13');
        $this->assertEquals($this->browser->getOS(), 'win');

        $this->browser->loadUserAgent('BlackBerry8110/4.3.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 VendorID/118');
        $this->assertEquals($this->browser->getOS(), 'blackberry');

        $this->browser->loadUserAgent('Palm680/RC1 Mozilla/4.0 (compatible; MSIE 6.0; Windows 98; PalmSource/Palm-D053; Blazer/4.5) 16;320x320 UP.Link/6.3.1.17.06.3.1.17.0');
        $this->assertEquals($this->browser->getOS(), 'palm');

        $this->browser->loadUserAgent('SAMSUNG-SGH-D900/1.0 Profile/MIDP-2.0 Configuration/CLDC-1.1 UP.Browser/6.2.3.3.c.1.101 (GUI) MMP/2.0 UP.Link/6.3.1.12.0');
        $this->assertEquals($this->browser->getOS(), 'samsung');

        $this->browser->loadUserAgent('SonyEricssonT280i/R1CB002 TelecaBrowser/Q04C1-1 Profile/MIDP-2.0 Configuration/CLDC-1.1');
        $this->assertEquals($this->browser->getOS(), 'sonyericsson');

        $this->browser->loadUserAgent('Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022; Lunascape 5.0.3.0)');
        $this->assertEquals($this->browser->getOS(), 'win');

        $this->browser->loadUserAgent('Mozilla/4.0 (compatible; MSIE 4.01; Windows CE; Sprint:SPH-ip320; Smartphone; 176x220)');
        $this->assertEquals($this->browser->getOS(), 'smartphone');

        $this->browser->loadUserAgent('w3m/0.5.2');
        $this->assertEquals($this->browser->getOS(), BrowserHandler::UNKNOWN);

        $this->browser->loadUserAgent('Uzbl (Webkit 1.1.12)');
        $this->assertEquals($this->browser->getOS(), BrowserHandler::UNKNOWN);

        $this->browser->loadUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; Avant Browser; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)');
        $this->assertEquals($this->browser->getOS(), 'win');

        $this->browser->loadUserAgent('Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.4; en; rv:1.9.0.19) Gecko/2010051911 Camino/2.0.3 (like Firefox/3.0.19)');
        $this->assertEquals($this->browser->getOS(), 'mac');

        $this->browser->loadUserAgent('Mozilla/5.0 (Macintosh; U; PPC Mac OS X; pl-PL; rv:1.0.1) Gecko/20021111 Chimera/0.6');
        $this->assertEquals($this->browser->getOS(), 'mac');

        $this->browser->loadUserAgent('Mozilla/5.0 (ipad Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.6 (KHTML, like Gecko) Chrome/7.0.498.0 Safari/534.6');
        $this->assertEquals($this->browser->getOS(), 'ipad');

        $this->browser->loadUserAgent('Mozilla/5.0 (iPhone; U; Linux i686; pt-br) AppleWebKit/532+ (KHTML, like Gecko) Version/3.0 Mobile/1A538b Safari/419.3 Midori/0.2.0');
        $this->assertEquals($this->browser->getOS(), 'iphone');
    }

    /**
     * Tests that the BrowserHandler detects all kinds of crawlers
     */
    public function testCrawlerDetection()
    {
        $userAgents = array(
                            'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322; Girafabot [girafa.com])',
                            'holmes/3.12.2 (http://morfeo.centrum.cz/bot)',
                            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
                            'Googlebot-Image/1.0',
                            'yacybot (x86 Windows XP 5.1; java 1.6.0_12; Europe/de) http://yacy.net/bot.html',
                            'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
                            'iaskspider/2.0(+http://iask.com/help/help_index.html)',
                            'ia_archiver/8.9 (Windows 3.9; en-US;)',
                            'Mozilla/2.0 (compatible; Ask Jeeves/Teoma; +http://sp.ask.com/docs/about/tech_crawling.html)',
                            'TurnitinBot/1.5 http://www.turnitin.com/robot/crawlerinfo.html',
                            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
                            'HappyFunBot/1.1 ( http://www.happyfunsearch.com/bot.html)',
                            'Mozilla/5.0 (compatible; KoepaBot BETA; http://www.koepa.nl/bot.html)',
                            'NetResearchServer/4.0(loopimprovements.com/robot.html)',
                            'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT 4.0; obot)',
                           );

        foreach ($userAgents as $agent)
        {
            // echo 'Testing: ' . $agent . PHP_EOL
            $this->browser->loadUserAgent($agent);
            $this->assertTrue($this->browser->isCrawler());
        }
    }

    /**
     * Tests that the BrowserHandler detects browsers engine
     */
    public function testEngineDetection()
    {
        $this->browser->loadUserAgent('Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0; Sleipnir/2.9.7)');
        $this->assertEquals($this->browser->getBrowserEngine(), 'trident');

        $this->browser->loadUserAgent('Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7)');
        $this->assertEquals($this->browser->getBrowserEngine(), 'trident');

        $this->browser->loadUserAgent('Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0');
        $this->assertEquals($this->browser->getBrowserEngine(), 'trident');

        $this->browser->loadUserAgent('Mozilla/4.0 (Windows; MSIE 7.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)');
        $this->assertEquals($this->browser->getBrowserEngine(), BrowserHandler::UNKNOWN);

        $this->browser->loadUserAgent('Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; fr) Presto/2.9.168 Version/11.52');
        $this->assertEquals($this->browser->getBrowserEngine(), 'presto');

        $this->browser->loadUserAgent('Opera/9.80 (Windows NT 5.1; U; cs) Presto/2.7.62 Version/11.01');
        $this->assertEquals($this->browser->getBrowserEngine(), 'presto');

        $this->browser->loadUserAgent('Opera/9.80 (Windows NT 6.1; U; en) Presto/2.6.30 Version/10.61');
        $this->assertEquals($this->browser->getBrowserEngine(), 'presto');

        $this->browser->loadUserAgent('Mozilla/6.0 (Macintosh; I; Intel Mac OS X 11_7_9; de-LI; rv:1.9b4) Gecko/2012010317 Firefox/10.0a4');
        $this->assertEquals($this->browser->getBrowserEngine(), 'gecko');

        $this->browser->loadUserAgent('Mozilla/5.0 (Windows NT 6.1; WOW64; rv:2.0b6pre) Gecko/20100903 Firefox/4.0b6pre');
        $this->assertEquals($this->browser->getBrowserEngine(), 'gecko');

        $this->browser->loadUserAgent('Mozilla/5.0 (Windows NT 6.1; rv:2.0) Gecko/20110319 Firefox/4.0');
        $this->assertEquals($this->browser->getBrowserEngine(), 'gecko');

        $this->browser->loadUserAgent('Mozilla/5.0 (compatible; Konqueror/3.5; NetBSD 4.0_RC3; X11) KHTML/3.5.7 (like Gecko)');
        $this->assertEquals($this->browser->getBrowserEngine(), 'khtml');

        $this->browser->loadUserAgent('Mozilla/5.0 (Windows; U; ; cs-CZ) (KHTML, like Gecko, Safari/528.0) Lunascape/5.1.1.0');
        $this->assertEquals($this->browser->getBrowserEngine(), 'khtml');

        $this->browser->loadUserAgent('Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/418.8 (KHTML, like Gecko, Safari) Cheshire/1.0.UNOFFICIAL');
        $this->assertEquals($this->browser->getBrowserEngine(), 'webkit');

        $this->browser->loadUserAgent('Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.801.0 Safari/535.1');
        $this->assertEquals($this->browser->getBrowserEngine(), 'webkit');

        $this->browser->loadUserAgent('Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1');
        $this->assertEquals($this->browser->getBrowserEngine(), 'webkit');

        $this->browser->loadUserAgent('NetResearchServer/4.0(loopimprovements.com/robot.html)');
        $this->assertEquals($this->browser->getBrowserEngine(), BrowserHandler::UNKNOWN);
    }

    /**
     * Tests that the BrowserHandler matches versions and names
     */
    public function testBrowserNameAndVersionDetection()
    {
        $userAgents = array(
                            // Internet Explorer
                            array('ua' => 'Mozilla/4.0 (compatible;MSIE 5.5; Windows 98)',
                                  'version' => '5',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/4.0 (compatible; MSIE 6.01; Windows NT 6.0)',
                                  'version' => '6',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'MMozilla/4.0 (X11; MSIE 6.0; i686; .NET CLR 1.1.4322; .NET CLR 2.0.50727; FDM)',
                                  'version' => '6',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/4.0 (Windows; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)',
                                  'version' => '6',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; fr-FR)',
                                  'version' => '7',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; SLCC2; .NET CLR 2.0.50727; InfoPath.3; .NET4.0C; .NET4.0E; .NET CLR 3.5.30729; .NET CLR 3.0.30729; MS-RTC LM 8)',
                                  'version' => '7',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.1; FDM; .NET CLR 1.1.4322)',
                                  'version' => '7',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)',
                                  'version' => '8',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)',
                                  'version' => '8',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)',
                                  'version' => '8',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; chromeframe/12.0.742.112)',
                                  'version' => '9',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)',
                                  'version' => '9',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; FunWebProducts)',
                                  'version' => '9',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
                                  'version' => '10',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)',
                                  'version' => '10',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/4.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)',
                                  'version' => '10',
                                  'name'    => BrowserHandler::IE),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0',
                                  'version' => '10',
                                  'name'    => BrowserHandler::IE),

                            // Firefox/Iceweasel ETC
                            array('ua' => 'Mozilla/5.0 (X11; U; Slackware Linux i686; en-US; rv:1.9.0.10) Gecko/2009042315 Firefox/3.0.10',
                                  'version' => '3',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; it; rv:1.9.1.16) Gecko/20101130 Firefox/3.5.16 GTB7.1 (.NET CLR 3.5.30729)',
                                  'version' => '3',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2) Gecko/20100222 Ubuntu/10.04 (lucid) Firefox/3.6',
                                  'version' => '3',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.2.13) Gecko/20110103 Fedora/3.6.13-1.fc14 Firefox/3.6.13',
                                  'version' => '3',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (X11; Linux x86_64; rv:2.0.1) Gecko/20110506 Firefox/4.0.1',
                                  'version' => '4',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.2a1pre) Gecko/20110323 Firefox/4.2a1pre',
                                  'version' => '4',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (Windows NT 5.1; rv:6.0) Gecko/20100101 Firefox/6.0 FirePHP/0.6',
                                  'version' => '6',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:9.0) Gecko/20100101 Firefox/9.0',
                                  'version' => '9',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/6.0 (Macintosh; I; Intel Mac OS X 11_7_9; de-LI; rv:1.9b4) Gecko/2012010317 Firefox/10.0a4',
                                  'version' => '10',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (X11; U; Linux sparc64; es-PY; rv:5.0) Gecko/20100101 IceCat/5.0 (like Firefox/5.0; Debian-6.0.1)',
                                  'version' => '5.0',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; it; rv:1.9.2.12) Gecko/20101114 IceCat/3.6.12 (like Firefox/3.6.12)',
                                  'version' => '3',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (X11; Linux x86_64; rv:11.0a2) Gecko/20111230 Firefox/11.0a2 Iceweasel/11.0a2',
                                  'version' => '11.0',
                                  'name'    => BrowserHandler::FIREFOX),
                            array('ua' => 'Mozilla/5.0 (X11; U; Linux i686; pt-PT; rv:1.9.2.3) Gecko/20100402 Iceweasel/3.6.3 (like Firefox/3.6.3) GTB7.0',
                                  'version' => '3',
                                  'name'    => BrowserHandler::FIREFOX),

                            // Browser Opera
                            array('ua' => 'Opera/9.60 (X11; Linux i686; U; ru) Presto/2.1.1',
                                  'version' => '9',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.61 (Windows NT 6.0; U; http://lucideer.com; en-GB) Presto/2.1.1',
                                  'version' => '9',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.63 (X11; Linux i686; U; de) Presto/2.1.1',
                                  'version' => '9',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.80 (X11; Linux i686; U; Debian; pl) Presto/2.2.15 Version/10.00',
                                  'version' => '10',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.80 (Windows NT 5.1; U; cs) Presto/2.2.15 Version/10.10',
                                  'version' => '10',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.80 (Windows NT 6.1; U; zh-cn) Presto/2.5.22 Version/10.50',
                                  'version' => '10',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.80 (Windows NT 6.1; U; zh-cn) Presto/2.7.62 Version/11.01',
                                  'version' => '11',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.80 (Windows NT 6.1; en) Presto/2.8.149 Version/11.1',
                                  'version' => '11',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; de) Opera 11.51',
                                  'version' => '11',
                                  'name'    => BrowserHandler::OPERA),
                            array('ua' => 'Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00',
                                  'version' => '12',
                                  'name'    => BrowserHandler::OPERA),

                            // Browser Chrome
                            array('ua' => 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Ubuntu/10.04 Chromium/14.0.813.0 Chrome/14.0.813.0 Safari/535.1',
                                  'version' => '14',
                                  'name'    => BrowserHandler::CHROME),
                            array('ua' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.824.0 Safari/535.1',
                                  'version' => '14',
                                  'name'    => BrowserHandler::CHROME),
                            array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.20 (KHTML, like Gecko) Chrome/19.0.1036.7 Safari/535.20',
                                  'version' => '19',
                                  'name'    => BrowserHandler::CHROME),
                            array('ua' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.21 (KHTML, like Gecko) Chrome/19.0.1042.0 Safari/535.21',
                                  'version' => '19',
                                  'name'    => BrowserHandler::CHROME),
                            array('ua' => 'Mozilla/5.0 (Windows NT 6.0) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.792.0 Safari/535.1',
                                  'version' => '14',
                                  'name'    => BrowserHandler::CHROME),
                            array('ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_5_8) AppleWebKit/534.31 (KHTML, like Gecko) Chrome/13.0.748.0 Safari/534.31',
                                  'version' => '13',
                                  'name'    => BrowserHandler::CHROME),
                            array('ua' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.24 (KHTML, like Gecko) Ubuntu/10.10 Chromium/12.0.703.0 Chrome/12.0.703.0 Safari/534.24',
                                  'version' => '12',
                                  'name'    => BrowserHandler::CHROME),
                            array('ua' => 'Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.127 Safari/534.16',
                                  'version' => '10',
                                  'name'    => BrowserHandler::CHROME),

                            // Browser Safari
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; cs-CZ) AppleWebKit/525.28.3 (KHTML, like Gecko) Version/3.2.3 Safari/525.29',
                                  'version' => '3',
                                  'name'    => BrowserHandler::SAFARI),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; ja-JP) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1',
                                  'version' => '4',
                                  'name'    => BrowserHandler::SAFARI),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19.1',
                                  'version' => '4',
                                  'name'    => BrowserHandler::SAFARI),
                            array('ua' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/531.21.11 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10',
                                  'version' => '4',
                                  'name'    => BrowserHandler::SAFARI),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; ja-JP) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16',
                                  'version' => '5',
                                  'name'    => BrowserHandler::SAFARI),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; tr-TR) AppleWebKit/533.18.1 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5',
                                  'version' => '5',
                                  'name'    => BrowserHandler::SAFARI),
                            array('ua' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; de-at) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1',
                                  'version' => '5',
                                  'name'    => BrowserHandler::SAFARI),
                            array('ua' => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-us) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
                                  'version' => '5',
                                  'name'    => BrowserHandler::SAFARI),

                            // Unknown or Uncommon Browsers
                            array('ua' => 'Mozilla/5.0 (compatible; U; ABrowse 0.6; Syllable) AppleWebKit/420+ (KHTML, like Gecko)',
                                  'version' => BrowserHandler::UNKNOWN,
                                  'name'    => BrowserHandler::UNKNOWN),
                            array('ua' => 'Mozilla/5.0 (Windows; U; WinNT; en; rv:1.0.2) Gecko/20030311 Beonex/0.8.2-stable',
                                  'version' => BrowserHandler::UNKNOWN,
                                  'name'    => BrowserHandler::UNKNOWN),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Win 9x 4.90; SG; rv:1.9.2.4) Gecko/20101104 Netscape/9.1.0285',
                                  'version' => '9',
                                  'name'    => BrowserHandler::NETSCAPE),
                            array('ua' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20050729 Netscape/8.0.3.3',
                                  'version' => '8',
                                  'name'    => BrowserHandler::NETSCAPE),
                            array('ua' => 'Mozilla/5.0 (compatible; Konqueror/4.5; FreeBSD) KHTML/4.5.4 (like Gecko)',
                                  'version' => '4',
                                  'name'    => BrowserHandler::KONQUEROR),
                            array('ua' => 'Mozilla/5.0 (compatible; Konqueror/3.1; i686 Linux; 20020811)',
                                  'version' => '3',
                                  'name'    => BrowserHandler::KONQUEROR),
                            array('ua' => 'Mozilla/5.0 (compatible; Konqueror/3.0-rc3; i686 Linux; 20020914)',
                                  'version' => '3',
                                  'name'    => BrowserHandler::KONQUEROR),
                            array('ua' => 'Midori/0.2.2 (X11; Linux i686; U; ja-jp) WebKit/531.2+',
                                  'version' => BrowserHandler::UNKNOWN,
                                  'name'    => BrowserHandler::UNKNOWN),
                            array('ua' => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.6) Gecko/2009030302 Minefield/3.0.6',
                                  'version' => BrowserHandler::UNKNOWN,
                                  'name'    => BrowserHandler::UNKNOWN),
                        );

        foreach ($userAgents as $agent)
        {
            //echo 'Testing: ' . $agent['ua'] . PHP_EOL;
            $this->browser->loadUserAgent($agent['ua']);
            $this->assertEquals($this->browser->getBrowserName(), $agent['name']);
            $this->assertEquals($this->browser->getBrowserVersion(), $agent['version']);
            $this->assertFalse($this->browser->isMobile());
        }
    }
}
?>
