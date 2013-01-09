<?php
/**
 * TestMainUserModule.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
use \Bolido\Modules\main\models\MainUserModule as User;

require_once(BASE_DIR . '/../modules/main/models/MainUserModule.php');
class TestMainUserModuler extends PHPUnit_Framework_TestCase
{
    /**
     * All of this are practically an interface tester.
     * for a pretty much dummy user.
     */

    public function testId()
    {
        $user = new User();
        $this->assertEquals($user->id(), 0);
    }

    public function testToken()
    {
        $user = new User();
        $this->assertEquals($user->token(), '');
    }

    public function testname()
    {
        $user = new User();
        $this->assertEquals($user->name(), '');
    }

    public function testGetData()
    {
        $user = new User();
        $this->assertEquals($user->getData(), array());
    }

    public function testLoadUserData()
    {
        $user = new User();
        $this->assertEquals($user->loadUserData(mt_rand(1, 200)), array());
    }

    public function testUpdate()
    {
        $user = new User();
        $this->assertEquals($user->update(array('hi' => 'ho'), mt_rand(1, 200)), false);
    }

    public function testCan()
    {
        $user = new User();
        $this->assertFalse($user->can('random_string'));
        $this->assertFalse($user->can('random_permission'));
        $this->assertFalse($user->can('other_random permission'));
        $this->assertFalse($user->can('dummy_place_holder'));
    }

    public function testIsLogged()
    {
        $user = new User();
        $this->assertFalse($user->isLogged());
    }
}
?>
