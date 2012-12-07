<?php
/**
 * TestTemplate.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require_once('../Source/Bolido/Template.php');
require_once('../Source/Bolido/Lang.php');
require_once('../Source/Bolido/Session.php');
require_once('../Source/Bolido/Hooks.php');

class MockLang extends \Bolido\App\Lang { public function __construct() {} public function free() { return null; } }
class MockSession extends \Bolido\App\Session { public function __construct() {} }
class MockHooks extends \Bolido\App\Hooks
{
    public function __construct() {}
    public function run()
    {
        if (func_num_args() > 0)
        {
            $args    = func_get_args();
            $section = strtolower($args['0']);
            array_shift($args);

            $return  = (isset($args['0']) ? $args['0'] : null);
            return $return;
        }
    }
}

class TestTemplate extends PHPUnit_Framework_TestCase
{
    protected $config, $lang, $session, $hooks;

    /**
     * Setup the environment
     */
    public function setUp()
    {
        $this->config = new TestConfig();
        $this->config->moduleDir = __DIR__ . '/..';
        $this->config->skin = 'default';
        $this->config->charset = 'UTF-8';

        $this->lang    = new MockLang();
        $this->hooks   = new MockHooks();
        $this->session = new MockSession();
    }

    /**
     * Test Normal behaviour
     */
    public function testNormalTemplate()
    {
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/normal');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Hello World</div>', $body);
    }

    /**
     * Test adding strings to templates
     */
    public function testStringTemplate()
    {
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/withString', array('string' => 'Hi'));

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Hi</div>', $body);
    }

    /**
     * Test adding strings to templates with setter method
     */
    public function testSetterTemplate()
    {
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/withString');
        $template->set('string', 'This is a nice long string');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>This is a nice long string</div>', $body);
    }

    /**
     * Test multiple templates
     */
    public function testMultipleTemplates()
    {
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/normal');
        $template->load('Workspace/withString', array('string' => 'Hello Universe'));

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Hello World</div><div>Hello Universe</div>', str_replace(array("\n", "\t"), '', $body));
    }

    /**
     * Test Skin choices
     */
    public function testSkins()
    {
        $this->config->skin = 'custom';
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/fallBack');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Skin Default</div>', $body);

        $this->config->skin = 'custom';
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/custom');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Skin Custom</div>', $body);

        $this->config->skin = 'default';
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/custom');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Skin Default</div>', $body);
    }

    /**
     * Test Reset
     */
    public function testClearTemplates()
    {
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/normal');
        $template->clear();

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertEmpty($body);
    }

    /**
     * Test Remove Templates
     */
    public function testTemplateRemoval()
    {
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/normal');
        $template->load('Workspace/custom');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Hello World</div><div>Skin Default</div>', str_replace(array("\n", "\t"), '', $body));
        $this->assertTrue($template->remove('Workspace/custom'));

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Hello World</div>', $body);
    }

    /**
     * Test extensions
     */
    public function testExtensions()
    {
        $template = new \Bolido\App\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->extend('addOne', function ($add) { return $add + 1; });
        $template->extend('returnSame', function($hi) { return $hi; });
        $template->extend('run', new MockHooks());
        $template->extend('free', new MockLang());

        $this->assertEquals(6, $template->addOne(5));
        $this->assertEquals(16, $template->addOne(15));
        $this->assertEquals(5, $template->returnSame(5));
        $this->assertEquals(5, $template->run('section', 5));
        $this->assertNull($template->free());
    }
}
?>
