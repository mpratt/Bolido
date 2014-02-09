<?php
/**
 * TestScannerAndCollection.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bolido\Filesystem\Scanner;
use Bolido\Filesystem\Resource;
use Bolido\Filesystem\Collection;

class TestScannerAndCollection extends PHPUnit_Framework_TestCase
{
    public function testScanner()
    {
        $scanner = new Scanner(new OutputterMock());
        $collection = $scanner->scan(SCANNER_DIR);
        $this->assertTrue($collection instanceof Collection);
        $this->assertEquals(5, count($collection->getFiles()));
        $this->assertEquals(3, count($collection->getDirectories()));

        $exclude = array('js$', 'md$', '5');
        $collection = $scanner->scan(SCANNER_DIR, $exclude);
        $this->assertEquals(2, count($collection->getFiles()));
        $this->assertEquals(3, count($collection->getDirectories()));

        $exclude = array('doc');
        $collection = $scanner->scan(SCANNER_DIR, $exclude);
        $this->assertEquals(0, count($collection->getFiles()));
        $this->assertEquals(3, count($collection->getDirectories()));

        $exclude = array('doc', 'dir');
        $collection = $scanner->scan(SCANNER_DIR, $exclude);
        $this->assertEquals(0, count($collection->getFiles()));
        $this->assertEquals(0, count($collection->getDirectories()));
    }

    public function testCollection()
    {
        $scanner = new Scanner(new OutputterMock());
        $collection = $scanner->scan(SCANNER_DIR);
        $files = $collection->getFiles();
        $dirs = $collection->getDirectories();

        $this->assertTrue($files instanceof Collection);
        $this->assertTrue($dirs instanceof Collection);

        foreach ($files as $f) {
            $this->assertTrue($f instanceof Resource);
            $this->assertTrue(is_file($f));
        }

        foreach ($dirs as $d) {
            $this->assertTrue($d instanceof Resource);
            $this->assertTrue(is_dir($d));
        }

        $files = $collection->getByCallback(function (Resource $res) {
            return ($res->getFilename() === 'doc1.md');
        });

        $this->assertCount(1, $files);
    }

    public function testInvalidScannerDir()
    {
        $this->setExpectedException('InvalidArgumentException');
        $scanner = new Scanner(new OutputterMock());
        $scanner->scan('/not/a/path');
    }

    public function testInvalidScannerFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        $scanner = new Scanner(new OutputterMock());
        $scanner->scan(WRITABLE_DIR . '/doc1.md');
    }
}

?>
