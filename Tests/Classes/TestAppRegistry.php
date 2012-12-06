<?php
/**
 * TestAppRegistry.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require_once('../Source/Bolido/AppRegistry.php');
class TestAppRegistry extends PHPUnit_Framework_TestCase
{
    /**
     * Test that the interface
     */
    public function testRegister()
    {
        $app = new \Bolido\App\AppRegistry();
        $app['array_object']  = (object) array('1' , '2', '3', '4', '5');
        $app['string_object'] = (object) 'Hello friends';
        $this->assertEquals($app['array_object'], (object) array('1', '2', '3', '4', '5'));
        $this->assertEquals($app['string_object'], (object) 'Hello friends');

        $objectArray  = (object) array('6', '7', '8', '9');
        $objectString = (object) 'Hello Again Friends';
        $app->attach('array_object_2', $objectArray);
        $app->attach('string_object_2', $objectString);
        $this->assertEquals($app['array_object_2'], $objectArray);
        $this->assertEquals($app['string_object_2'], $objectString);
    }

    /**
     * Test references
     */
    public function testReferences()
    {
        $config = new TestConfig();
        $config->mainUrl = 'http://www.hablarmierda.net';

        $app = new \Bolido\App\AppRegistry();
        $app->attach('object_reference', $config);
        $app['object_by_array'] = $config;
        $this->assertEquals($config, $app['object_reference'], $app['object_by_array']);

        $config->mainUrl = 'http://www.michael-pratt.com';
        $this->assertEquals($config->mainUrl, $app['object_reference']->mainUrl, $app['object_by_array']->mainUrl);

        $app['object_reference']->mainUrl = 'http://www.eltiempo.com';
        $this->assertEquals($config->mainUrl, $app['object_reference']->mainUrl, $app['object_by_array']->mainUrl);

        $app['object_by_array']->mainUrl = 'http://www.stackoverflow.com';
        $this->assertEquals($config->mainUrl, $app['object_reference']->mainUrl, $app['object_by_array']->mainUrl);
    }
}
?>
