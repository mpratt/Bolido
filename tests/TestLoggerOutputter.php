<?php
/**
 * TestLoggerOutputter.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bolido\Outputter\Logger;

class TestLoggerOutputter extends PHPUnit_Framework_TestCase
{
    // Properties where log files are stored
    protected $logDir, $logFile, $logDirPossibleFile;

    public function setUp()
    {
        $this->logDirPossibleFile = WRITABLE_DIR . '/bolido-' . date('Y-m-d') . '.log';
        $this->logFile = WRITABLE_DIR . '/logger-test.log';
        $this->logDir = WRITABLE_DIR;

        $this->cleanEnvironment();
    }

    public function tearDown()
    {
        $this->cleanEnvironment();
    }

    public function testLoggerDefaultFile()
    {
        $log = new Logger($this->logDir);
        $log->write('hello');
        $log->write('world');

        $this->assertTrue(file_exists($this->logDirPossibleFile));
        $this->assertContains('hello', file_get_contents($this->logDirPossibleFile));
        $this->assertContains('world', file_get_contents($this->logDirPossibleFile));
    }

    public function testLoggerCustomFile()
    {
        touch($this->logFile);
        $log = new Logger($this->logFile);
        $log->write('hello');
        $log->write('world');

        $this->assertTrue(file_exists($this->logFile));
        $this->assertContains('hello', file_get_contents($this->logFile));
        $this->assertContains('world', file_get_contents($this->logFile));
    }

    public function testInvalidFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Logger('/');
    }

    protected function cleanEnvironment()
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        if (file_exists($this->logDirPossibleFile)) {
            unlink($this->logDirPossibleFile);
        }
    }

}

?>
