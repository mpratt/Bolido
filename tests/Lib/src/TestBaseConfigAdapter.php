<?php
/**
 * TestBaseConfigAdapter.php
 *
 * @package Tests
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestBaseConfigAdapter extends PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $config = new TestConfig();
        $config->cacheMode = 'unknown cache engine';
        $config->mainUrl = 'http://www.hola.com/';

        $config->initialize();
        $this->assertEquals('http://www.hola.com', $config->mainUrl);
        $this->assertTrue(isset($config->sourceDir));
        $this->assertTrue(isset($config->moduleDir));
        $this->assertTrue(isset($config->cacheDir));
        $this->assertTrue(isset($config->logsDir));
        $this->assertEquals(SOURCE_DIR, $config->sourceDir);
        $this->assertEquals(MODULE_DIR, $config->moduleDir);
        $this->assertEquals(CACHE_DIR, $config->cacheDir);
        $this->assertEquals(LOGS_DIR, $config->logsDir);
        $this->assertEquals(BASE_DIR . '/assets/Uploads', $config->uploadsDir);
        $this->assertEquals($config->mainUrl . '/assets/Uploads', $config->uploadsDirUrl);
        $this->assertEquals('default', $config->skin);
        $this->assertEquals('en', $config->language);
        $this->assertEquals('en', $config->fallbackLanguage);
        $this->assertEquals(array('en'), $config->allowedLanguages);
        $this->assertEquals('file', $config->cacheMode);


        $config = new TestConfig();
        $config->mainUrl = 'http://www.hola.com/casa';
        $config->language = 'es';
        $config->fallbackLanguage = 'en';
        $config->allowedLanguages = array('de');

        $config->initialize();
        $this->assertEquals('http://www.hola.com/casa', $config->mainUrl);
        $this->assertEquals(SOURCE_DIR, $config->sourceDir);
        $this->assertEquals(MODULE_DIR, $config->moduleDir);
        $this->assertEquals(CACHE_DIR, $config->cacheDir);
        $this->assertEquals(LOGS_DIR, $config->logsDir);
        $this->assertEquals(BASE_DIR . '/assets/Uploads', $config->uploadsDir);
        $this->assertEquals($config->mainUrl . '/assets/Uploads', $config->uploadsDirUrl);
        $this->assertEquals('default', $config->skin);
        $this->assertEquals('es', $config->language);
        $this->assertEquals('en', $config->fallbackLanguage);
        $this->assertEquals(array('de', 'es', 'en'), $config->allowedLanguages);
        $this->assertEquals('file', $config->cacheMode);

        $config = new TestConfig();
        $config->cacheMode = 'Apc';

        $config->initialize();
        $this->assertEquals('apc', $config->cacheMode);
    }

    public function testGet()
    {
        $config = new TestConfig();
        $config->mainUrl = 'http://www.hola.com/';
        $this->assertEquals('http://www.hola.com/', $config->mainUrl);

        $config->thisPropertyDoesntExist = 'yep';
        $this->assertEquals('yep', $config->thisPropertyDoesntExist);

        $this->setExpectedException('InvalidArgumentException');
        $config->thisPropertyAlsoDoesntExist;
    }
}
?>
