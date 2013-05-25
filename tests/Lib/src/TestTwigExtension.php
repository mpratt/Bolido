<?php
/**
 * TestTwigExtension.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestTwigExtension extends PHPUnit_Framework_TestCase
{
    public function testExtensionFilters()
    {
        $ext = new \Bolido\Twig\Extension(new TestContainer());
        $ret = $ext->getFilters();

        foreach ($ret as $r)
        {
            $this->assertTrue(is_object($r));
        }
    }

    public function testExtensionFunctions()
    {
        $ext = new \Bolido\Twig\Extension(new TestContainer());
        $ret = $ext->getFunctions();

        foreach ($ret as $r)
        {
            $this->assertTrue(is_object($r));
        }
    }

    public function testExtensionGlobals()
    {
        if (!defined('CANONICAL_URL'))
            define('CANONICAL_URL', 'Hey');

        $ext = new \Bolido\Twig\Extension(new TestContainer());
        $ret = $ext->getGlobals();

        $this->assertCount(3, $ret);
        $this->assertTrue(is_object($ret['config']));
        $this->assertTrue(is_object($ret['session']));
        $this->assertTrue(is_string($ret['canonical']));
    }
}
?>
