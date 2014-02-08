<?php
/**
 * TestConfig.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bolido\Config;

class TestConfig extends PHPUnit_Framework_TestCase
{
    public function testDemoConfig()
    {
        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => __DIR__ . '/demo/public/',
            'url_prefix' => '/home/prefix/long/'
        );

        $config = new Config($config);
        $this->assertEquals($config['layout_dir'], __DIR__ . '/demo/demo-site/layouts');
        $this->assertEquals($config['plugin_dir'], __DIR__ . '/demo/demo-site/plugins');
        $this->assertEquals($config['source_dir'], __DIR__ . '/demo/demo-site');
        $this->assertEquals($config['output_dir'], __DIR__ . '/demo/public');
        $this->assertEquals($config['url_prefix'], '/home/prefix/long');
    }

    public function testRelativeOutputDir()
    {
        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => '../public/',
        );

        $config = new Config($config);
        $this->assertEquals($config['layout_dir'], __DIR__ . '/demo/demo-site/layouts');
        $this->assertEquals($config['plugin_dir'], __DIR__ . '/demo/demo-site/plugins');
        $this->assertEquals($config['source_dir'], __DIR__ . '/demo/demo-site');
        $this->assertEquals($config['output_dir'], __DIR__ . '/demo/public');
    }

    public function testInvalidSource()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => '/unknown-directory/yay/',
            'output_dir' => '../public/',
        );

        $config = new Config($config);
    }


    public function testInvalidSourceNotDirectory()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => __FILE__,
            'output_dir' => '../public/',
        );

        $config = new Config($config);
    }

    public function testInvalidOutputDir()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => '/unknown-directory/yay/',
        );

        $config = new Config($config);
    }

    public function testInvalidNotWritableOutputDir()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => '/',
        );

        $config = new Config($config);
    }

    public function testInvalidLayoutDir()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => '../public/',
            'layout_dir' => '/unknown-dir/',
        );

        $config = new Config($config);
    }

    public function testInvalidPluginDir()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => '../public/',
            'plugin_dir' => '/unknown-dir/',
        );

        $config = new Config($config);
    }

    public function testDirectiveSetter()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => '../public/',
        );

        $config = new Config($config);
        $config['a_new_key'] = 'should fail';
    }


    public function testUnsettedDirectiveGetter()
    {
        $this->setExpectedException('InvalidArgumentException');

        $config = array(
            'source_dir' => __DIR__ . '/demo/demo-site/',
            'output_dir' => '../public/',
        );

        $config = new Config($config);
        unset($config['source_dir']);
        $config['source_dir'];
    }
}

?>
