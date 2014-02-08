<?php
/**
 * TestResource.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bolido\Filesystem\Resource;

class TestResource extends PHPUnit_Framework_TestCase
{
    public function testFile()
    {
        $file = RESOURCE_DIR . '/markdown.MD';
        $relative = str_replace(__DIR__, '', $file);

        $resource = new Resource($file, $relative);
        $this->assertEquals('md', $resource->getExtension());
        $this->assertEquals($relative, $resource->getRelativePath());
        $this->assertEquals(dirname($relative), $resource->getRelativePath(true));
        $this->assertTrue($resource->isIndexable());
        $this->assertEquals('markdown', $resource->getFilenameShort(true));
        $this->assertEquals('markdown.MD', $resource->getFilenameShort(false));
        $this->assertTrue((bool) preg_match('~---~', $resource->getContents()));
    }

    public function testDatePrefix()
    {
        $file = RESOURCE_DIR . '/2014-02-07_markdown.md';
        $relative = str_replace(__DIR__, '', $file);

        $resource = new Resource($file, $relative);
        $this->assertEquals('md', $resource->getExtension());
        $this->assertEquals($relative, $resource->getRelativePath());
        $this->assertEquals(dirname($relative), $resource->getRelativePath(true));
        $this->assertTrue($resource->isIndexable());
        $this->assertEquals('markdown', $resource->getFilenameShort(true));
        $this->assertEquals('markdown.md', $resource->getFilenameShort(false));
        $this->assertTrue((bool) preg_match('~---~', $resource->getContents()));
    }

    public function testOnlyDateFile()
    {
        $file = RESOURCE_DIR . '/2014-02-07.md';
        $relative = str_replace(__DIR__, '', $file);

        $resource = new Resource($file, $relative);
        $this->assertEquals('md', $resource->getExtension());
        $this->assertEquals($relative, $resource->getRelativePath());
        $this->assertEquals(dirname($relative), $resource->getRelativePath(true));
        $this->assertTrue($resource->isIndexable());
        $this->assertEquals('2014-02-07', $resource->getFilenameShort(true));
        $this->assertEquals('2014-02-07.md', $resource->getFilenameShort(false));
    }

    public function testNoExtension()
    {
        $file = RESOURCE_DIR . '/no-extension';
        $relative = str_replace(__DIR__, '', $file);

        $resource = new Resource($file, $relative);
        $this->assertEquals('', $resource->getExtension());
        $this->assertEquals($relative, $resource->getRelativePath());
        $this->assertEquals(dirname($relative), $resource->getRelativePath(true));
        $this->assertFalse($resource->isIndexable());
        $this->assertEquals('no-extension', $resource->getFilenameShort(true));
        $this->assertEquals('no-extension', $resource->getFilenameShort(false));
    }

    public function testHtmlFile()
    {
        $file = RESOURCE_DIR . '/index.html';
        $relative = str_replace(__DIR__, '', $file);

        $resource = new Resource($file, $relative);
        $this->assertEquals('html', $resource->getExtension());
        $this->assertEquals($relative, $resource->getRelativePath());
        $this->assertEquals(dirname($relative), $resource->getRelativePath(true));
        $this->assertFalse($resource->isIndexable());
        $this->assertEquals('index', $resource->getFilenameShort(true));
        $this->assertEquals('index.html', $resource->getFilenameShort(false));
    }

    public function testDirectory()
    {
        $file = RESOURCE_DIR . '/';
        $relative = str_replace(__DIR__, '', $file);

        $resource = new Resource($file, $relative);
        $this->assertEquals('', $resource->getExtension());
        $this->assertEquals(rtrim($relative, '/'), $resource->getRelativePath());
        $this->assertEquals(rtrim($relative, '/'), $resource->getRelativePath(true));
        $this->assertFalse($resource->isIndexable());
        $this->assertEquals(basename($file), $resource->getFilenameShort(true));
        $this->assertEquals(basename($file), $resource->getFilenameShort(false));
    }
}

?>
