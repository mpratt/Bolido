<?php
/**
 * TestTwigLocator.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestTwigLocator extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->config = new TestConfig();
        $this->config->moduleDir = MODULE_DIR . '/modules';
        $this->config->skin = 'default';
    }

    public function testSource()
    {
        $l = new \Bolido\Twig\Locator($this->config);
        $source = $l->getSource('fake/hi');
        $this->assertEquals('Hola Mundo Twig!!!', trim($source));
    }

    public function testInvalidSource()
    {
        $this->setExpectedException('Exception');

        $l = new \Bolido\Twig\Locator($this->config);
        $source = $l->getSource('fake/unknown');
    }

    public function testCache()
    {
        $l = new \Bolido\Twig\Locator($this->config);
        $key = $l->getCacheKey('fake/hi');

        $this->assertEquals(md5(MODULE_DIR . '/modules/fake/templates/default/hi.twig'), $key);
    }

    public function testFresh()
    {
        $l = new \Bolido\Twig\Locator($this->config);
        $this->assertFalse($l->isFresh('fake/hi', time() - 1000000));
        $this->assertTrue($l->isFresh('fake/hi', filemtime(MODULE_DIR . '/modules/fake/templates/default/hi.twig')));
    }
}
?>
