<?php
/**
 * TestErrorHandler.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestErrorHandler extends PHPUnit_Framework_TestCase
{
    protected $hooks, $template, $logFile;

    /**
     * SetUp the environment
     */
    public function setUp()
    {
        $this->hooks = new MockHooks();
        $this->template = new MockTemplate();
        $this->logFile = LOGS_DIR . '/errors-' . date('Y-m-d') . '.log';
        @unlink($this->logFile);
    }

    /**
     * Test SaveMessage
     */
    public function testSavedMessage()
    {
        $error = new \Bolido\ErrorHandler($this->hooks, $this->template);
        $error->saveMessage('Hi');
        $this->assertEquals($error->totalErrors(), 1);

        $error->saveMessage('Hi');
        $this->assertEquals($error->totalErrors(), 1);

        $error->saveMessage('Hi', 'this is a backtrace');
        $this->assertEquals($error->totalErrors(), 2);

        $error->saveMessage('Hi', 'this is a backtrace');
        $this->assertEquals($error->totalErrors(), 2);

        $error->saveMessage('Hi', 'this is a backtrace 2');
        $this->assertEquals($error->totalErrors(), 3);
    }

    /**
     * Test WriteLog
     */
    public function testWriteLog()
    {
        $error = new \Bolido\ErrorHandler($this->hooks, $this->template);
        $error->saveMessage('Hi');
        $this->assertEquals($error->totalErrors(), 1);

        $error->saveMessage('Hi');
        $this->assertEquals($error->totalErrors(), 1);

        $error->writeLog();
        $this->assertEquals($error->totalErrors(), 0);

        $file = file($this->logFile);
        $this->assertEquals(count($file), 1);

        $error->saveMessage('Hello', 'My Friend');
        $this->assertEquals($error->totalErrors(), 1);

        $error->writeLog();
        $this->assertEquals($error->totalErrors(), 0);

        $file = file($this->logFile);
        $this->assertEquals(count($file), 2);
        $this->assertContains('Hi', $file['0']);
        $this->assertContains(date('Y-m-d'), $file['0']);
        $this->assertContains('127.0.0.99', $file['0']);
        $this->assertContains('Hello', $file['1']);
        $this->assertContains('My Friend', $file['1']);
        $this->assertContains(date('Y-m-d'), $file['1']);
        $this->assertContains('127.0.0.99', $file['1']);

        @unlink($this->logFile);
    }
}

?>
