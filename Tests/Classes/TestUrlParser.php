<?php
/**
 * TestUrlParser.php
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
    define('BOLIDO', 'TestUrlParser');

require_once(dirname(__FILE__) . '/../Config-Test.php');
require_once(dirname(__FILE__) . '/../../Bolido/Sources/UrlParser.class.php');

if (!function_exists('redirectTo'))
{
    function redirectTo($url, $type = false) { return $url; }
}

class TestUrlParser extends PHPUnit_Framework_TestCase
{
    protected $config;

    /**
     * Setup the Environment
     */
    public function setUp()
    {
        ob_start();
        $this->config = new TestConfig();
        $this->config->set('mainurl', 'http://www.example.com');
        $this->config->set('language', 'en');
        $this->config->set('allowedLanguages', array('es', 'en', 'de'));
    }

    public function tearDown() { ob_end_clean(); }

    /**
     * Test that languages get stripped from the uri string
     */
    public function testDetectLanguage()
    {
        $urlParser = new UrlParser('/', $this->config);
        $this->assertEquals($urlParser->detectLanguage('/en/module/action/index.php'), $this->config->get('mainurl') . '/module/action/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/', $this->config);
        $this->assertEquals($urlParser->detectLanguage('/ex/module/action/'), $this->config->get('mainurl') . '/module/action/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/', $this->config);
        $this->assertEquals($urlParser->detectLanguage('/es/module/action'), '/module/action/');
        $this->assertEquals($this->config->get('language'), 'es');

        $urlParser = new UrlParser('/', $this->config);
        $this->assertEquals($urlParser->detectLanguage('/de/module/action/'), '/module/action/');
        $this->assertEquals($this->config->get('language'), 'de');
    }

    /**
     * Test that the UrlParser Class can check url consistency
     */
    public function testConsistency()
    {
        $_SERVER['HTTP_HOST'] = 'http://example.com';
        $urlParser = new UrlParser('/moduleName1/?hiiii=bua', $this->config);
        $this->assertEquals($urlParser->validateUrlConsistency(), $this->config->get('mainurl') . '/moduleName1/?hiiii=bua');

        $_SERVER['HTTP_HOST'] = 'http://www.example.com/hi/blu/true';
        $urlParser = new UrlParser('/moduleName2', $this->config);
        $this->assertEquals($urlParser->validateUrlConsistency(), $this->config->get('mainurl') . '/moduleName2/');

        $urlParser = new UrlParser('/moduleName3?hi=bua', $this->config);
        $this->assertEquals($urlParser->validateUrlConsistency(), $this->config->get('mainurl') . '/moduleName3/?hi=bua');

        $urlParser = new UrlParser('/', $this->config);
        $this->assertNull($urlParser->validateUrlConsistency());

        $urlParser = new UrlParser('/modulename/action/', $this->config);
        $this->assertNull($urlParser->validateUrlConsistency());

        $urlParser = new UrlParser('hi/', $this->config);
        $this->assertNull($urlParser->validateUrlConsistency());
    }

    /**
     * Test that UrlParser returns a correct path
     */
    public function testGetPath()
    {
        $urlParser = new UrlParser('/ModuleName/ActionName/?query=hi&bu=tro', $this->config);
        $this->assertEquals($urlParser->getPath(), '/ModuleName/ActionName/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/es/ModuleName/ActionName/?query=hi&bu=tro', $this->config);
        $this->assertEquals($urlParser->getPath(), '/ModuleName/ActionName/');
        $this->assertEquals($this->config->get('language'), 'es');

        $this->setUp();
        $urlParser = new UrlParser('/ModuleName/ActionName/index.php', $this->config);
        $this->assertEquals($urlParser->getPath(), '/ModuleName/ActionName/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/?hiiii=buuu', $this->config);
        $this->assertEquals($urlParser->getPath(), '/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/ex/ModuleName/ActionName/?query=hi&bu=tro', $this->config);
        $this->assertEquals($urlParser->getPath(), $this->config->get('mainurl') . '/ModuleName/ActionName/?query=hi&bu=tro');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/sdf/?query=hi&bu=tro', $this->config);
        $this->assertEquals($urlParser->getPath(), '/sdf/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/en/ModuleName/ActionName/', $this->config);
        $this->assertEquals($urlParser->getPath(), $this->config->get('mainurl') . '/ModuleName/ActionName/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/ModuleName/ActionName/OtherAction/YetAnother', $this->config);
        $this->assertEquals($urlParser->getPath(), '/ModuleName/ActionName/OtherAction/YetAnother/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/__/hi', $this->config);
        $this->assertEquals($urlParser->getPath(), '/__/hi/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/$?&^?%a?ds?d*#$$%##@$@#SER#$%?query=hi&bu?adad/dfgdfg?/dfgdfgdfgpojdfg/?tro', $this->config);
        $this->assertEquals($urlParser->getPath(), '/$/');
        $this->assertEquals($this->config->get('language'), 'en');

        $urlParser = new UrlParser('/-', $this->config);
        $this->assertEquals($urlParser->getPath(), '/-/');
        $this->assertEquals($this->config->get('language'), 'en');

        $this->config->set('mainurl', 'http://www.example.com/path/to/stuff');
        $urlParser = new UrlParser('/path/to/stuff/ModuleName/Hi', $this->config);
        $this->assertEquals($urlParser->getPath(), '/ModuleName/Hi/');
        $this->assertEquals($this->config->get('language'), 'en');

        $this->config->set('mainurl', 'http://www.example.com/path/to/stuff');
        $urlParser = new UrlParser('/path/to/stuff/es/ModuleName/Yes/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/ModuleName/Yes/');
        $this->assertEquals($this->config->get('language'), 'es');

        $this->config->set('mainurl', 'http://www.example.com/path/to/stuff');
        $urlParser = new UrlParser('/path/to/stuff/', $this->config);
        $this->assertEquals($urlParser->getPath(), '/');
        $this->assertEquals($this->config->get('language'), 'es');

        $this->config->set('mainurl', 'http://www.example.com/path/to/stuff');
        $urlParser = new UrlParser('/path/to/stuff', $this->config);
        $this->assertEquals($urlParser->getPath(), '/');
        $this->assertEquals($this->config->get('language'), 'es');
    }

    /**
     * Test the object detects canonical urls
     */
    public function testgetCanonical()
    {
        $urlParser = new UrlParser('/module/?token=adsasdasdasd', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->get('mainurl') . '/module/');

        $urlParser = new UrlParser('/es/module/?PHPSESSID=adsasdasdasd', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->get('mainurl') . '/es/module/');

        $urlParser = new UrlParser('/module/?token=adsasdasdasd&mike=yes', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->get('mainurl') . '/module/?mike=yes');

        $urlParser = new UrlParser('/module', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->get('mainurl') . '/module/');

        $urlParser = new UrlParser('/module/?token=adsasdasdasd&phpsessid=765', $this->config);
        $this->assertEquals($urlParser->getCanonical(), $this->config->get('mainurl') . '/module/?phpsessid=765');
    }


}
?>
