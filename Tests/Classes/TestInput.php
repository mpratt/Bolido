<?php
/**
 * TestInput.php
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
    define('BOLIDO', 'TestInput');

require_once(dirname(__FILE__) . '/../../Bolido/Sources/Input.class.php');

class TestInput extends PHPUnit_Framework_TestCase
{
    protected $input;

    /**
     * Setup the Environment
     */
    public function setUp()
    {
        $_POST = array('post_string' => 'a string sent Via post',
                       'post_array'  => array('value1', 'value2', 'value3'),
                       'post_empty'  => '');

        $_GET = array('get_string' => 'a string sent Via get',
                      'get_array'  => array('value1', 'value2', 'value3'),
                      'get_empty'  => '');

        $_COOKIE = array('cookie_string' => 'a string sent Via Cookies',
                          'cookie_array'  => array('value1', 'value2', 'value3'),
                          'cookie_empty'  => '');


        $_FILES   = array('files_string' => 'a string sent Via Files',
                          'files_array'  => array('value1', 'value2', 'value3'),
                          'files_empty'  => '');

        $this->input = new Input();
    }

    /**
     * Clean the environment after this test
     */
    public function tearDown()
    {
        $_POST = $_GET = $_COOKIE = $_FILES = $_REQUEST = array();
    }

    /**
     * Test $_POST array
     */
    public function testPost()
    {
        $this->assertTrue($this->input->hasPost('post_string'));
        $this->assertTrue($this->input->hasPost('post_array'));
        $this->assertTrue($this->input->hasPost('post_empty'));

        $this->assertEquals($this->input->post('post_string'), 'a string sent Via post');
        $this->assertEquals($this->input->post('post_array'), array('value1', 'value2', 'value3'));
        $this->assertEquals($this->input->post('post_empty'), '');
    }

    /**
     * Test $_GET array
     */
    public function testGet()
    {
        $this->assertTrue($this->input->hasGet('get_string'));
        $this->assertTrue($this->input->hasGet('get_array'));
        $this->assertTrue($this->input->hasGet('get_empty'));

        $this->assertEquals($this->input->get('get_string'), 'a string sent Via get');
        $this->assertEquals($this->input->get('get_array'), array('value1', 'value2', 'value3'));
        $this->assertEquals($this->input->get('get_empty'), '');
    }

    /**
     * Test $_REQUEST array which in the input class is a merge between $_POST and $_GET
     */
    public function testRequest()
    {
        $this->assertTrue($this->input->hasRequest('post_string'));
        $this->assertTrue($this->input->hasRequest('post_array'));
        $this->assertTrue($this->input->hasRequest('post_empty'));
        $this->assertTrue($this->input->hasRequest('get_string'));
        $this->assertTrue($this->input->hasRequest('get_array'));
        $this->assertTrue($this->input->hasRequest('get_empty'));

        $this->assertEquals($this->input->request('post_string'), 'a string sent Via post');
        $this->assertEquals($this->input->request('post_array'), array('value1', 'value2', 'value3'));
        $this->assertEquals($this->input->request('post_empty'), '');
        $this->assertEquals($this->input->request('get_string'), 'a string sent Via get');
        $this->assertEquals($this->input->request('get_array'), array('value1', 'value2', 'value3'));
        $this->assertEquals($this->input->request('get_empty'), '');
    }

    /**
     * Test $_COOKIE array
     */
    public function testCookie()
    {
        $this->assertTrue($this->input->hasCookie('cookie_string'));
        $this->assertTrue($this->input->hasCookie('cookie_array'));
        $this->assertTrue($this->input->hasCookie('cookie_empty'));

        $this->assertEquals($this->input->cookie('cookie_string'), 'a string sent Via Cookies');
        $this->assertEquals($this->input->cookie('cookie_array'), array('value1', 'value2', 'value3'));
        $this->assertEquals($this->input->cookie('cookie_empty'), '');
    }

    /**
     * Test $_FILES array
     */
    public function testFiles()
    {
        $this->assertTrue($this->input->hasFiles('files_string'));
        $this->assertTrue($this->input->hasFiles('files_array'));
        $this->assertTrue($this->input->hasFiles('files_empty'));

        $this->assertEquals($this->input->files('files_string'), 'a string sent Via Files');
        $this->assertEquals($this->input->files('files_array'), array('value1', 'value2', 'value3'));
        $this->assertEquals($this->input->files('files_empty'), '');
    }
}
?>
