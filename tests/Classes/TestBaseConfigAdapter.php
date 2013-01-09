<?php
/**
 * TestBaseConfigAdapter.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestBaseConfigAdapter extends PHPUnit_Framework_TestCase
{

    /**
     * Test Initialization
     */
    public function testInit()
    {
        $config = new TestConfig();
        $config->mainUrl = 'http://www.hola.com/';

        $config->initialize();
        $this->assertEquals('http://www.hola.com', $config->mainUrl);
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
    }

    /**
     *
     */
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
