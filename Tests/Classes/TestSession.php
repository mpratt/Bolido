<?php
/**
 * TestSession.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require_once('../Source/Bolido/Session.php');
class TestSession extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the setter and getter methods
     */
    public function testSetterGetter()
    {
        $session = new \Bolido\App\Session();
        $session->setName('CustomName');
        $this->assertTrue($session->start());
        $this->assertTrue($session->isStarted());
        $this->assertEquals($session->getName(), 'CustomName');

        $session = new \Bolido\App\Session();
        $this->assertTrue($session->start());
        $this->assertTrue($session->isStarted());
        $this->assertEquals($session->getName(), 'BOLIDOSESSID');

        $session = new \Bolido\App\Session();
        $this->assertTrue($session->start());
        $session->set('string_value', 'My Name is Bólido');
        $session->set('object_value', (object) array('My Object'));
        $session->set('array_value', array('1', '2', '3'));
        $this->assertEquals($session->get('string_value'), 'My Name is Bólido');
        $this->assertEquals($session->get('object_value'), (object) array('My Object'));
        $this->assertEquals($session->get('array_value'), array('1', '2', '3'));
        $this->assertTrue($session->has('string_value'));
        $this->assertTrue($session->has('object_value'));
        $this->assertTrue($session->has('array_value'));
        $this->assertFalse($session->has('unknown_key'));
        $this->assertFalse($session->has('other_unset_key'));
        $this->assertTrue($session->isStarted());
        $this->assertEquals($session->getName(), 'BOLIDOSESSID');

        $session->reset();
        $this->assertFalse($session->has('string_value'));
        $this->assertFalse($session->has('object_value'));
        $this->assertFalse($session->has('array_value'));
        $this->assertFalse($session->has('unknown_key'));
        $this->assertFalse($session->has('other_unset_key'));
    }
}
?>