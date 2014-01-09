<?php
/**
 * TestSiteGeneration.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TestSiteGeneration extends PHPUnit_Framework_TestCase
{
    protected $publicDir;
    protected $sourceDir;

    /**
     * Set up the test environment
     */
    public function setUp()
    {
        $this->publicDir = __DIR__ . '/demo/public';
        $this->sourceDir = __DIR__ . '/demo/demo-site';
        $this->tearDown();
    }

    /**
     * Clean up the test environment
     */
    public function tearDown()
    {
        $dir = new RecursiveDirectoryIterator($this->publicDir, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($it as $path) {
            if (preg_match('~placeholder~', $path->getPathname())) {
                continue ;
            } elseif ($path->isFile()) {
                unlink($path->getPathname());
            } else {
                rmdir($path->getPathname());
            }
        }
    }

    protected function createInstance($config)
    {
        $outputter = new \Bolido\Outputter\Logger(dirname($this->publicDir));
        $bolido = new \Bolido\Bolido($outputter, $config);
        $bolido->setScanner(new \Bolido\Filesystem\Scanner($outputter))
            ->setFileSystem(new \Bolido\Filesystem\Filesystem($outputter));

        return $bolido;
    }

    public function testStuff()
    {
        $config = array(
            'source_dir' => $this->sourceDir,
            'output_dir' => $this->publicDir
        );

        $bolido = $this->createInstance($config);
        $bolido->create();
    }
}

?>
