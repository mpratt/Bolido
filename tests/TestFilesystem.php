<?php
/**
 * TestFileSystem.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bolido\Filesystem\Resource;
use Bolido\Filesystem\Filesystem;

class TestFileSystem extends PHPUnit_Framework_TestCase
{
    public function testBehaviour()
    {
        $fs = new Filesystem(new OutputterMock());
        $this->assertTrue($fs->mkdir(WRITABLE_DIR . '/custom_dir'));
        $this->assertTrue($fs->exists(WRITABLE_DIR . '/custom_dir'));
        $this->assertTrue($fs->mkdir(WRITABLE_DIR . '/custom_dir'));
        $this->assertTrue($fs->exists(WRITABLE_DIR . '/custom_dir'));
        $this->assertTrue($fs->unlink(WRITABLE_DIR . '/custom_dir'));
        $this->assertFalse($fs->exists(WRITABLE_DIR . '/custom_dir'));

        $this->assertTrue($fs->mkdir(WRITABLE_DIR . '/recursive/directory'));
        $this->assertTrue($fs->exists(WRITABLE_DIR . '/recursive'));
        $this->assertTrue($fs->exists(WRITABLE_DIR . '/recursive/directory'));
        $this->assertTrue($fs->unlink(WRITABLE_DIR . '/recursive/directory'));
        $this->assertTrue($fs->unlink(WRITABLE_DIR . '/recursive'));
        $this->assertFalse($fs->exists(WRITABLE_DIR . '/recursive'));

        $this->assertTrue((bool) $fs->copyFromString('Hello World', WRITABLE_DIR . '/hello-world.md'));
        $this->assertEquals('Hello World', file_get_contents(WRITABLE_DIR . '/hello-world.md'));
        $this->assertTrue((bool) $fs->copyFromString('Hello Universe', WRITABLE_DIR . '/hello-world.md'));
        $this->assertEquals('Hello Universe', file_get_contents(WRITABLE_DIR . '/hello-world.md'));
        $this->assertTrue($fs->unlink(WRITABLE_DIR . '/hello-world.md'));

        $this->assertTrue((bool) $fs->copyFromString('Hello World', WRITABLE_DIR . '/recursive/hello-world.md'));
        $this->assertTrue($fs->exists(WRITABLE_DIR . '/recursive/hello-world.md'));
        $this->assertTrue($fs->unlink(WRITABLE_DIR . '/recursive/hello-world.md'));
        $this->assertTrue($fs->unlink(WRITABLE_DIR . '/recursive/'));

        $this->assertTrue($fs->exists(RESOURCE_DIR . '/index.html'));
        $this->assertTrue($fs->copy(new Resource(RESOURCE_DIR . '/index.html'), WRITABLE_DIR . '/index2.html'));
        $this->assertTrue($fs->exists(WRITABLE_DIR . '/index2.html'));
        $this->assertEquals(file_get_contents(RESOURCE_DIR . '/index.html'), file_get_contents(WRITABLE_DIR . '/index2.html'));
        $this->assertTrue($fs->unlink(WRITABLE_DIR . '/index2.html'));
    }
}

?>
