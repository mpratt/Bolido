<?php
/**
 * TestContainerIoC.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestContainerIoC extends PHPUnit_Framework_TestCase
{
    public function testContainerMain()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/';

        $config = new TestConfig();
        $config->initialize();

        $container = new \Bolido\Container($config, new MockBenchMark());

        $this->assertTrue(is_object($container['config']));
        $this->assertTrue(is_object($container['session']));
        $this->assertTrue(is_object($container['urlparser']));
        $this->assertTrue(is_object($container['router']));
        $this->assertTrue(is_object($container['cache']));
        $this->assertTrue(is_object($container['file_cache']));
        $this->assertTrue(is_object($container['apc_cache']));
        $this->assertTrue(is_object($container['db']));
        $this->assertTrue(is_object($container['error']));
        $this->assertTrue(is_object($container['lang']));
        $this->assertTrue(is_object($container['user']));
        $this->assertTrue(is_object($container['twig_locator']));
        $this->assertTrue(is_object($container['twig_extension']));
        $this->assertTrue(is_object($container['twig']));
    }

    public function testUnknownUserModule()
    {
        $this->setExpectedException('InvalidArgumentException');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/';

        $config = new TestConfig();
        $config->initialize();
        $config->usersModule = '\Bolido\Container';

        $container = new \Bolido\Container($config, new MockBenchMark());

        $this->assertTrue(is_object($container['lang']));
        $this->assertTrue(is_object($container['user']));
    }

}
?>
