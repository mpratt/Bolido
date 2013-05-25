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

class TestLang extends PHPUnit_Framework_TestCase
{
    protected $config;

    public function setUp()
    {
        $this->config = new TestConfig();
        $this->config->moduleDir = MODULE_DIR . '/modules';
        $this->config->language = 'es';
        $this->config->fallbackLanguage = 'es';
    }

    public function testLangKeyExists()
    {
        $lang = new \Bolido\Lang($this->config);

        $this->assertTrue($lang->load('fake/testLang'));
        $this->assertTrue($lang->exists('hello'));
        $this->assertTrue($lang->exists('friends'));
        $this->assertTrue($lang->exists('say_hi'));
        $this->assertTrue($lang->exists('say_bye'));
        $this->assertTrue($lang->exists('you_are'));
        $this->assertTrue($lang->exists('greeting_name'));
        $this->assertEquals($lang->getCurrentLanguage(), 'es');
    }

    public function testFindLanguageFile()
    {
        $lang = new \Bolido\Lang($this->config);

        $this->assertTrue($lang->load('fake/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hola');
        $this->assertEquals($lang->get('friends'), 'Amigos');
        $this->assertEquals($lang->get('say_hi'), 'Hola Amigos');
        $this->assertEquals($lang->get('say_bye'), 'Adios Amigos');
        $this->assertEquals($lang->get('you_are', 'genio'), 'Eres un genio');
        $this->assertEquals($lang->get('greeting_name', 'Amigos', 'Mike'), 'Hola Amigos mi nombre es Mike');
        $this->assertEquals($lang->getCurrentLanguage(), 'es');
    }

    public function testLanguageArrayInput()
    {
        $lang = new \Bolido\Lang($this->config);

        $this->assertTrue($lang->load('fake/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hola');
        $this->assertEquals($lang->get('friends'), 'Amigos');
        $this->assertEquals($lang->get('say_hi'), 'Hola Amigos');
        $this->assertEquals($lang->get('say_bye'), 'Adios Amigos');
        $this->assertEquals($lang->get('you_are', 'genio'), 'Eres un genio');
        $this->assertEquals($lang->get('greeting_name', 'Amigos', 'Mike'), 'Hola Amigos mi nombre es Mike');
        $this->assertEquals($lang->getCurrentLanguage(), 'es');

        $this->assertEquals($lang->get(array('hello')), 'Hola');
        $this->assertEquals($lang->get(array('friends')), 'Amigos');
        $this->assertEquals($lang->get(array('you_are', 'genio')), 'Eres un genio');
        $this->assertEquals($lang->get(array('greeting_name', 'Amigos', 'Mike')), 'Hola Amigos mi nombre es Mike');
    }

    public function testFindLanguageFileByModule()
    {
        $lang = new \Bolido\Lang($this->config);

        $this->assertTrue($lang->load('fake/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hola');
        $this->assertEquals($lang->get('friends'), 'Amigos');
        $this->assertEquals($lang->get('say_hi'), 'Hola Amigos');
        $this->assertEquals($lang->get('say_bye'), 'Adios Amigos');
        $this->assertEquals($lang->get('you_are', 'genio'), 'Eres un genio');
        $this->assertEquals($lang->get('greeting_name', 'Amigos', 'Mike'), 'Hola Amigos mi nombre es Mike');
        $this->assertEquals($lang->getCurrentLanguage(), 'es');
    }

    public function testFindLanguageFileByLang()
    {
        $this->config->language = 'en';
        $lang = new \Bolido\Lang($this->config);

        $this->assertTrue($lang->load('fake/testLang'));
        $this->assertEquals($lang->get('hello'), 'Hello!');
        $this->assertEquals($lang->get('friends'), 'Friends');
        $this->assertEquals($lang->get('say_hi'), 'Hi friends');
        $this->assertEquals($lang->get('say_bye'), 'Bye Friends');
        $this->assertEquals($lang->get('you_are', 'genius'), 'You are a genius');
        $this->assertEquals($lang->get('greeting_name', 'Folks', 'Mike'), 'Hi Folks my name is Mike');
        $this->assertEquals($lang->getCurrentLanguage(), 'en');

        $this->config->language = 'es';
    }

    public function testFindLanguageFileByFallback()
    {
        $this->config->language = 'en';
        $this->config->fallbackLanguage = 'es';
        $lang = new \Bolido\Lang($this->config);

        $this->assertTrue($lang->load('fake/fallBackExample'));
        $this->assertEquals($lang->get('example_1'), 'La Mamá y el Papá');
        $this->assertEquals($lang->get('example_2'), '@#$%&*()$%&**');
        $this->assertEquals($lang->get('example_3'), '_-^&*~');
        $this->assertEquals($lang->getCurrentLanguage(), 'en');
    }

    public function testUnknownKeys()
    {
        $this->config->language = 'en';
        $lang = new \Bolido\Lang($this->config);

        $this->assertTrue($lang->load('fake/testLang'));

        $this->assertTrue($lang->exists('hello'));
        $this->assertTrue($lang->exists('friends'));
        $this->assertTrue($lang->exists('say_hi'));
        $this->assertTrue($lang->exists('say_bye'));
        $this->assertTrue($lang->exists('you_are'));
        $this->assertTrue($lang->exists('greeting_name'));
        $this->assertEquals($lang->get('hello'), 'Hello!');
        $this->assertEquals($lang->get('friends'), 'Friends');
        $this->assertEquals($lang->get('say_hi'), 'Hi friends');
        $this->assertEquals($lang->get('say_bye'), 'Bye Friends');
        $this->assertEquals($lang->get('you_are', 'genius'), 'You are a genius');
        $this->assertEquals($lang->get('greeting_name', 'Folks', 'Mike'), 'Hi Folks my name is Mike');
        $this->assertEquals($lang->getCurrentLanguage(), 'en');

        $lang->free();
        $this->assertFalse($lang->exists('hello'));
        $this->assertFalse($lang->exists('friends'));
        $this->assertFalse($lang->exists('say_hi'));
        $this->assertFalse($lang->exists('say_bye'));
        $this->assertFalse($lang->exists('you_are'));
        $this->assertFalse($lang->exists('greeting_name'));
        $this->assertEquals($lang->get('hello'), 'hello');
        $this->assertEquals($lang->get('friends'), 'friends');
        $this->assertEquals($lang->get('say_hi'), 'say_hi');
        $this->assertEquals($lang->get('say_bye'), 'say_bye');
        $this->assertEquals($lang->get('you_are', 'genius'), 'you_are');
        $this->assertEquals($lang->get('greeting_name', 'Folks', 'Mike'), 'greeting_name');
        $this->assertEquals($lang->getCurrentLanguage(), 'en');

        $this->assertFalse($lang->load('fake/UnexistantLangFile'));
    }
}
?>
