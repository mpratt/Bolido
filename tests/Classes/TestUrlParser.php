<?php
/**
 * TestUrlParser.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestUrlParser extends \PHPUnit_Framework_TestCase
{
    protected $config;

    /**
     * Setup the Environment
     */
    public function setUp() { $this->config = new TestConfig(); }

    /**
     * Test that the url parser gets the right paths
     */
    public function testPaths()
    {
        $this->config->mainUrl = 'http://example.com';
        $urlParser = new \Bolido\UrlParser('/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/');

        $this->config->mainUrl = 'http://example.com';
        $urlParser = new \Bolido\UrlParser('/main/index/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/main/index/');

        $this->config->mainUrl = 'http://example.com';
        $urlParser = new \Bolido\UrlParser('/main/index/?hellow&pallow/rallow', $this->config);
        $this->assertEquals($urlParser->getPath(), '/main/index/');

        $this->config->mainUrl = 'http://example.com/custom/path';
        $urlParser = new \Bolido\UrlParser('/custom/path/main/index/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/main/index/');

        $this->config->mainUrl = 'http://www.example.com/path/to/stuff/';
        $urlParser = new \Bolido\UrlParser('/path/to/stuff/hellow', $this->config);
        $this->assertEquals($urlParser->getPath(), '/hellow/');

        $this->config->mainUrl = 'http://example.com';
        $urlParser = new \Bolido\UrlParser('/home/index-1/hi.html', $this->config);
        $this->assertEquals($urlParser->getPath(), '/home/index-1/hi.html/');

        $this->config->mainUrl = 'http://example.com';
        $urlParser = new \Bolido\UrlParser('/stuff&more/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/stuff&more/');

        $this->config->mainUrl = 'http://example.com';
        $urlParser = new \Bolido\UrlParser('/class/2012-12-03?stuff=hi&module=234', $this->config);
        $this->assertEquals($urlParser->getPath(), '/class/2012-12-03/');

        $this->config->mainUrl = 'http://example.com/';
        $urlParser = new \Bolido\UrlParser('/index.php', $this->config);
        $this->assertEquals($urlParser->getPath(), '/');

        $this->config->mainUrl = 'http://example.com';
        $urlParser = new \Bolido\UrlParser('/hello/world/inDex.php', $this->config);
        $this->assertEquals($urlParser->getPath(), '/hello/world/');

        $this->config->mainUrl = 'http://example.com/very/long/path/';
        $urlParser = new \Bolido\UrlParser('/very/long/path/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/');

        $urlParser = new \Bolido\UrlParser('/very/long/path/hello/worlds', $this->config);
        $this->assertEquals($urlParser->getPath(), '/hello/worlds/');

        $this->config->mainUrl = 'http://example.com/';
        $urlParser = new \Bolido\UrlParser('/$&52$24?23454#$%RERG', $this->config);
        $this->assertEquals($urlParser->getPath(), '/$&52$24/');

        $this->config->mainUrl = 'http://example.com/million/dollars/';
        $urlParser = new \Bolido\UrlParser('/million/dollars/baby/movie/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/baby/movie/');

        $this->config->mainUrl = 'http://www.example.com';
        $urlParser = new \Bolido\UrlParser('/very-strange-path/with/__3459345/nu.mb.ers', $this->config);
        $this->assertEquals($urlParser->getPath(), '/very-strange-path/with/__3459345/nu.mb.ers/');
    }

    /**
     * Test for Url Consistency
     */
    public function testUrlConsistency()
    {
        $this->config->mainUrl = 'http://www.example.com';
        $_SERVER['HTTP_HOST']  = 'example.com';
        $urlParser = new \Bolido\UrlParser('/very-strange-path/', $this->config);
        $this->assertTrue($urlParser->urlNotConsistent());

        $this->config->mainUrl = 'http://www.example.com';
        $_SERVER['HTTP_HOST']  = 'www.example.com';
        $urlParser = new \Bolido\UrlParser('/very-strange-path', $this->config);
        $this->assertTrue($urlParser->urlNotConsistent());

        $this->config->mainUrl = 'http://www.example.com/path/';
        $_SERVER['HTTP_HOST']  = 'www.example.com';
        $urlParser = new \Bolido\UrlParser('/very-strange-path/', $this->config);
        $this->assertFalse($urlParser->urlNotConsistent());

        $this->config->mainUrl = 'http://www.example.com';
        $_SERVER['HTTP_HOST']  = 'www.example.com';
        $urlParser = new \Bolido\UrlParser('/', $this->config);
        $this->assertFalse($urlParser->urlNotConsistent());

        $this->config->mainUrl = 'http://www.example.com';
        $_SERVER['HTTP_HOST']  = 'www.example.com';
        $urlParser = new \Bolido\UrlParser('/very-strange-path/no-ending-slash', $this->config);
        $this->assertTrue($urlParser->urlNotConsistent());

        $this->config->mainUrl = 'http://www.example.com';
        $_SERVER['HTTP_HOST']  = 'www.exAmple.com';
        $urlParser = new \Bolido\UrlParser('/very-strange-path/', $this->config);
        $this->assertFalse($urlParser->urlNotConsistent());

        $this->config->mainUrl = 'http://www.example.com';
        $_SERVER['HTTP_HOST']  = 'example.com';
        $urlParser = new \Bolido\UrlParser('/', $this->config);
        $this->assertTrue($urlParser->urlNotConsistent());

        /**
         * Lets do some language checks
         */
        $this->config->language = 'es';
        $this->config->allowedLanguages = array('es', 'en');
        $this->config->mainUrl = 'http://www.example.com';
        $_SERVER['HTTP_HOST']  = 'www.example.com';

        $urlParser = new \Bolido\UrlParser('/?locale=en', $this->config);
        $this->assertFalse($urlParser->urlNotConsistent());

        $urlParser = new \Bolido\UrlParser('/?locale=es', $this->config);
        $this->assertTrue($urlParser->urlNotConsistent());

        $urlParser = new \Bolido\UrlParser('/?locale=ex', $this->config);
        $this->assertTrue($urlParser->urlNotConsistent());
    }

    /**
     * Test for canonical urls consistency
     */
    public function testCanonicalUrls()
    {
        $this->config->mainUrl = 'http://www.example.com';
        $urlParser = new \Bolido\UrlParser('/very-strange-path/with/__3459345/nu.mb.ers', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/very-strange-path/with/__3459345/nu.mb.ers/');

        $urlParser = new \Bolido\UrlParser('/?action=module&module=main', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/?action=module&amp;module=main');

        $urlParser = new \Bolido\UrlParser('/module/Action/?PHPSESSID=234254564545645&id=3', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/module/Action/?id=3');

        $urlParser = new \Bolido\UrlParser('/?BOLIDOSESSID=345345345&token=456456456', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/');

        $urlParser = new \Bolido\UrlParser('/Path/index.php?BOLIDOSESSID=565656', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/Path/');

        $urlParser = new \Bolido\UrlParser('/normal/path', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/normal/path/');

        $urlParser = new \Bolido\UrlParser('/MoDuLE/?hello=world', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/MoDuLE/?hello=world');

        $urlParser = new \Bolido\UrlParser('/Path?harlem=yes', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/Path/?harlem=yes');

        $this->config->language = 'en';
        $this->config->allowedLanguages = array('en', 'es');
        $urlParser = new \Bolido\UrlParser('/path?locale=en', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/path/');

        $urlParser = new \Bolido\UrlParser('/path/?locale=es', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/path/?locale=es');

        $urlParser = new \Bolido\UrlParser('/path?locale=de', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/path/');

        $urlParser = new \Bolido\UrlParser('/path?locale=stuff', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->mainUrl . '/path/');
    }
}
?>
