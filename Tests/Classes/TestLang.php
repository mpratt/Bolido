<?php
/**
 * TestLang.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require_once('../Source/Bolido/Lang.php');
class TestLang extends PHPUnit_Framework_TestCase
{
    protected $config;

    /**
     * Prepare the environement
     */
    public function setUp()
    {
        $this->config = new TestConfig();
        $this->config->moduleDir = __DIR__ . '/../';
        $this->config->language = 'es';
        $this->config->fallbackLanguage = 'es';
    }

    /**
     * Tests if a lang file is loaded based only on its filename
     */
    public function testLangKeyExists()
    {
        $lang = new \Bolido\App\Lang($this->config);

        $this->assertTrue($lang->load('Workspace/testLang'));
        $this->assertTrue($lang->exists('hello'));
        $this->assertTrue($lang->exists('friends'));
        $this->assertTrue($lang->exists('say_hi'));
        $this->assertTrue($lang->exists('say_bye'));
        $this->assertTrue($lang->exists('you_are'));
        $this->assertTrue($lang->exists('greeting_name'));
    }

    /**
     * Tests if a lang file is loaded based only on its filename
     */
    public function testFindLanguageFile()
    {
        $lang = new \Bolido\App\Lang($this->config);

        $this->assertTrue($lang->load('Workspace/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hola');
        $this->assertEquals($lang->get('friends'), 'Amigos');
        $this->assertEquals($lang->get('say_hi'), 'Hola Amigos');
        $this->assertEquals($lang->get('say_bye'), 'Adios Amigos');
        $this->assertEquals($lang->get('you_are', 'genio'), 'Eres un genio');
        $this->assertEquals($lang->get('greeting_name', 'Amigos', 'Mike'), 'Hola Amigos mi nombre es Mike');
    }

    /**
     * Tests if a lang file is loaded based only on its modulename and filename
     */
    public function testFindLanguageFileByModule()
    {
        $lang = new \Bolido\App\Lang($this->config);

        $this->assertTrue($lang->load('Workspace/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hola');
        $this->assertEquals($lang->get('friends'), 'Amigos');
        $this->assertEquals($lang->get('say_hi'), 'Hola Amigos');
        $this->assertEquals($lang->get('say_bye'), 'Adios Amigos');
        $this->assertEquals($lang->get('you_are', 'genio'), 'Eres un genio');
        $this->assertEquals($lang->get('greeting_name', 'Amigos', 'Mike'), 'Hola Amigos mi nombre es Mike');
    }

    /**
     * Tests if a lang file is loaded based only on its modulename, filename and language
     */
    public function testFindLanguageFileByLang()
    {
        $this->config->language = 'en';
        $lang = new \Bolido\App\Lang($this->config);

        $this->assertTrue($lang->load('Workspace/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hello!');
        $this->assertEquals($lang->get('friends'), 'Friends');
        $this->assertEquals($lang->get('say_hi'), 'Hi friends');
        $this->assertEquals($lang->get('say_bye'), 'Bye Friends');
        $this->assertEquals($lang->get('you_are', 'genius'), 'You are a genius');
        $this->assertEquals($lang->get('greeting_name', 'Folks', 'Mike'), 'Hi Folks my name is Mike');

        $this->config->language = 'es';
    }

    /**
     * Tests if a lang file is loaded based only on its modulename, filename and fallbacklanguage
     */
    public function testFindLanguageFileByFallback()
    {
        $this->config->language = 'en';
        $this->config->fallbackLanguage = 'es';
        $lang = new \Bolido\App\Lang($this->config);

        $this->assertTrue($lang->load('Workspace/fallBackExample'));
        $this->assertEquals($lang->get('example_1'), 'La Mamá y el Papá');
        $this->assertEquals($lang->get('example_2'), '@#$%&*()$%&**');
        $this->assertEquals($lang->get('example_3'), '_-^&*~');
    }
}
?>
