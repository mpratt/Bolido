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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $this->assertNull($template->setContentType('string'));
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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/fallBack');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Skin Default</div>', $body);

        $this->config->skin = 'custom';
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/custom');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Skin Custom</div>', $body);

        $this->config->skin = 'default';
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/normal');
        $template->load('Workspace/custom');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>Hello World</div><div>Skin Default</div>', str_replace(array("\n", "\t"), '', $body));
        $template->remove('Workspace/custom');
        $template->remove('Workspace/custom');

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
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->extend('addOne', function ($add) { return $add + 1; });
        $template->extend('returnSame', function($hi) { return $hi; });
        $template->extend('run', array(new MockHooks(), 'run'));
        $template->extend('free', array(new MockLang(), 'free'));

        $this->assertEquals(6, $template->addOne(5));
        $this->assertEquals(16, $template->addOne(15));
        $this->assertEquals(5, $template->returnSame(5));
        $this->assertEquals(5, $template->run('section', 5));
        $this->assertNull($template->free());
    }

    /**
     * Test invalid extensions, trying to overwrite object methods
     */
    public function testInvalidExtensions()
    {
        $this->setExpectedException('InvalidArgumentException');

        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->extend('addOne', function ($add) { return $add + 1; });
        $template->extend('display', function($hi) { return $hi; });
    }

    /**
     * Test invalid extensions, trying to overwrite
     * already registered methods
     */
    public function testInvalidExtensions2()
    {
        $this->setExpectedException('InvalidArgumentException');

        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->extend('addOne', function ($add) { return $add + 1; });
        $template->extend('addTwo', function ($add) { return $add + 1; });
        $template->extend('addone', function($hi) { return $hi; });
    }

    /**
     * Test uncollable Extensions
     */
    public function testInvalidExtensions3()
    {
        $this->setExpectedException('Exception');

        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->extend('addOne', array('1', '2'));
    }

    /**
     * Test uncollable Extensions
     */
    public function testInvalidExtensions4()
    {
        $this->setExpectedException('RuntimeException');

        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->UnexisTantMethod();
    }

    /**
     * Test Lazy loading
     */
    public function testLazyTemplate1()
    {
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/withString', array('string' => 'lazy 1'), true);
        $template->load('Workspace/withString', array('string' => 'lazy 3'), true);
        $template->load('Workspace/withString', array('string' => 'lazy 2'), true);
        $template->load('Workspace/withString', array('string' => 'lazy 5'), true);

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>lazy 1</div><div>lazy 3</div><div>lazy 2</div><div>lazy 5</div>', str_replace(array("\n", "\t", "\r"), '', $body));
    }

    /**
     * Test Lazy loading
     */
    public function testLazyTemplate2()
    {
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/withString', array('string' => 'lazy 1'));
        $template->load('Workspace/withString', array('string' => 'lazy 2'), true);
        $template->load('Workspace/withString', array('string' => 'lazy 3'), true);
        $template->load('Workspace/withString'); // This one is not going to show

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>lazy 1</div><div>lazy 2</div><div>lazy 3</div>', str_replace(array("\n", "\t", "\r"), '', $body));
    }

    /**
     * Test Lazy loading
     */
    public function testLazyTemplate3()
    {
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/withString', array('string' => 'lazy 1'));
        $template->load('Workspace/normal');
        $template->load('Workspace/withString', array('string' => 'lazy 2'), true);
        $template->load('Workspace/normal', array(), true);
        $template->load('Workspace/withString', array('string' => 'lazy 3'), true);

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>lazy 1</div><div>Hello World</div><div>lazy 2</div><div>Hello World</div><div>lazy 3</div>', str_replace(array("\n", "\t", "\r"), '', $body));
    }

    /**
     * Test Lazy loading
     */
    public function testLazyTemplate4()
    {
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('<hello friends>', array(), true);
        $template->load('Workspace/normal');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<hello friends><div>Hello World</div>', str_replace(array("\n", "\t", "\r"), '', $body));
    }

    /**
     * Test Invalid Template File
     */
    public function testInvalidTemplate()
    {
        $this->setExpectedException('InvalidArgumentException');

        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->load('Workspace/unexistant-normal');
    }

    /**
     * Test Overwrite setter
     */
    public function testOverwriteSetter()
    {
        $this->setExpectedException('InvalidArgumentException');

        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->set('key1', 'value1');
        $template->set('key1', 'value1');
    }

    /**
     * Test Overwrite setter
     */
    public function testOverwriteSetter2()
    {
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->set('string', 'value 1');
        $template->load('Workspace/withString', array('string' => 'lazy 1'));

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>value 1</div>', str_replace(array("\n", "\t", "\r"), '', $body));
    }

    /**
     * Test Overwrite setter
     */
    public function testOverwriteSetter3()
    {
        $template = new \Bolido\Template($this->config, $this->lang, $this->session, $this->hooks);
        $template->set('string', 'value 1');
        $template->set('string', 'value 2', true);
        $template->load('Workspace/withString');

        ob_start();
        $template->display();
        $body = ob_get_contents();
        ob_end_clean();

        $this->assertContains('<div>value 2</div>', str_replace(array("\n", "\t", "\r"), '', $body));
    }
}
?>
