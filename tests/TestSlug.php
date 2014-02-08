<?php
/**
 * TestSlug.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bolido\Config;
use Bolido\Filesystem\Resource;
use Bolido\Utils\Slug;

class TestSlug extends PHPUnit_Framework_TestCase
{
    public function testFromString()
    {
        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => __DIR__ . '/demo/public/',
        );

        $slug = new Slug(new Config($config));
        $this->assertEquals('/index.html', $slug->fromString('/'));
        $this->assertEquals('/index.html', $slug->fromString('////'));
        $this->assertEquals('/a-normal-text.html', $slug->fromString('a NoRmAL text'));
        $this->assertEquals('/www/folder/index.html', $slug->fromString('/www/folder/index'));
        $this->assertEquals('/home/a-nice-url.html', $slug->fromString('/homé/á\' nícë Úrl'));
        $this->assertEquals('/hi-you.html', $slug->fromString('hi   you'));
        $this->assertEquals('/a-nice-long-text/index.html', $slug->fromString('A nice long text/'));
        $this->assertEquals('/index.html', $slug->fromString(''));
    }

    public function testFromResource()
    {
        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => __DIR__ . '/demo/public/',
        );

        $slug = new Slug(new Config($config));

        $file = RESOURCE_DIR . '/2014-02-07_markdown.md';
        $relative = str_replace(__DIR__, '', $file);
        $resource = new Resource($file, $relative);
        $this->assertEquals($slug->fromResource($resource), '/assets/resource/markdown.html');

        $file = RESOURCE_DIR . '/2014-02-07.md';
        $relative = str_replace(__DIR__, '', $file);
        $resource = new Resource($file, $relative);
        $this->assertEquals($slug->fromResource($resource), '/assets/resource/2014-02-07.html');
    }

    public function testUrlPrefix()
    {
        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => __DIR__ . '/demo/public/',
            'url_prefix' => '/prefix',
        );

        $slug = new Slug(new Config($config));
        $this->assertEquals('/prefix/index.html', $slug->fromString('/'));
        $this->assertEquals('/prefix/index.html', $slug->fromString('////'));
        $this->assertEquals('/prefix/a-normal-text.html', $slug->fromString('a NoRmAL text'));
        $this->assertEquals('/prefix/www/folder/index.html', $slug->fromString('/www/folder/index'));
        $this->assertEquals('/prefix/home/a-nice-url.html', $slug->fromString('/homé/á\' nícë Úrl'));
        $this->assertEquals('/prefix/hi-you.html', $slug->fromString('hi   you'));
        $this->assertEquals('/prefix/a-nice-long-text/index.html', $slug->fromString('A nice long text/'));
        $this->assertEquals('/prefix/index.html', $slug->fromString(''));
    }

    public function testUrlPrefix2()
    {
        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => __DIR__ . '/demo/public/',
            'url_prefix' => '/another-prefix/',
        );

        $slug = new Slug(new Config($config));
        $this->assertEquals('/another-prefix/index.html', $slug->fromString('/'));
        $this->assertEquals('/another-prefix/index.html', $slug->fromString('////'));
        $this->assertEquals('/another-prefix/a-normal-text.html', $slug->fromString('a NoRmAL text'));
        $this->assertEquals('/another-prefix/www/folder/index.html', $slug->fromString('/www/folder/index'));
        $this->assertEquals('/another-prefix/home/a-nice-url.html', $slug->fromString('/homé/á\' nícë Úrl'));
        $this->assertEquals('/another-prefix/hi-you.html', $slug->fromString('hi   you'));
        $this->assertEquals('/another-prefix/a-nice-long-text/index.html', $slug->fromString('A nice long text/'));
        $this->assertEquals('/another-prefix/index.html', $slug->fromString(''));
    }

    public function testSlugExtensionReplacement()
    {
        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => __DIR__ . '/demo/public/',
        );

        $slug = new Slug(new Config($config));
        $slug->addExtension('md', 'html');
        $slug->addExtension('markdown', 'html');
        $slug->addExtension('tmp', 'jpg');
        $this->assertEquals('/file.html', $slug->fromString('file.md'));
        $this->assertEquals('/file.html', $slug->fromString('file.markdown'));
        $this->assertEquals('/file.jpg', $slug->fromString('file.tmp'));

        $slug->removeExtension('tmp');
        $this->assertEquals('/file.html', $slug->fromString('file.tmp'));
    }
}

?>
