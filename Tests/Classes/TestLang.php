<?php
/**
 * TestLang.php
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
    define('BOLIDO', 'TestLang');

if (class_exists('Config'))
    require_once(dirname(__FILE__) . '/../Config-Test.php');

require_once(dirname(__FILE__) . '/../../Bolido/Sources/Hooks.class.php');
require_once(dirname(__FILE__) . '/../../Bolido/Sources/Lang.class.php');

class TestLang extends PHPUnit_Framework_TestCase
{
    protected $hooks;
    protected $config;

    /**
     * Prepare the environement
     */
    public function setUp()
    {
        $this->hooks = $this->getMockBuilder('Hooks')->disableOriginalConstructor()->getMock();

        $this->config = new Config();
        $this->config->set('moduledir', realpath(dirname(__FILE__) . '/..'));
        $this->config->set('language', 'es');
        $this->config->set('fallbackLanguage', 'es');
    }

    /**
     * Tests if a lang file is loaded based only on its filename
     */
    public function testLangKeyExists()
    {
        $lang = new Lang($this->config, $this->hooks, 'Workspace');

        $this->assertTrue($lang->load('testLang'));
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
        $lang = new Lang($this->config, $this->hooks, 'Workspace');

        $this->assertTrue($lang->load('testLang'));
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
        $lang = new Lang($this->config, $this->hooks, 'UnknownModuleName');

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
        $this->config->set('language', 'en');
        $lang = new Lang($this->config, $this->hooks, 'UnknownModuleName');

        $this->assertTrue($lang->load('Workspace/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hello!');
        $this->assertEquals($lang->get('friends'), 'Friends');
        $this->assertEquals($lang->get('say_hi'), 'Hi friends');
        $this->assertEquals($lang->get('say_bye'), 'Bye Friends');
        $this->assertEquals($lang->get('you_are', 'genius'), 'You are a genius');
        $this->assertEquals($lang->get('greeting_name', 'Folks', 'Mike'), 'Hi Folks my name is Mike');

        $this->config->set('language', 'es');
    }

    /**
     * Tests if a lang file is loaded based only on its modulename, filename and fallbacklanguage
     */
    public function testFindLanguageFileByFallback()
    {
        $this->config->set('language', 'en');
        $this->config->set('fallbackLanguage', 'es');
        $lang = new Lang($this->config, $this->hooks, 'Workspace');

        $this->assertTrue($lang->load('fallBackExample'));
        $this->assertEquals($lang->get('example_1'), 'La Mamá y el Papá');
        $this->assertEquals($lang->get('example_2'), '@#$%&*()$%&**');
        $this->assertEquals($lang->get('example_3'), '_-^&*~');
    }
}
?>
