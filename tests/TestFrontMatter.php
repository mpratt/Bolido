<?php
/**
 * TestFrontMatter.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bolido\Filesystem\Resource;
use Bolido\Utils\FrontMatter;

class TestFrontMatter extends PHPUnit_Framework_TestCase
{
    public function testMatterDetection()
    {
        $files = array(
            'large-matter-separator.md',
            'multiple-matter-separator.md',
            'one-matter-separator.md',
        );

        foreach ($files as $f) {
            $this->check(FRONT_MATTER_DIR . '/' . $f);
        }
    }

    public function testInvalidMatter()
    {
        $matter = new FrontMatter(new Resource(FRONT_MATTER_DIR . '/invalid-matter.md'));
        $m = $matter->getMatter();
        $c = $matter->getContents();

        $this->assertTrue(!empty($c));
        $this->assertTrue(empty($m));
        $this->assertTrue(is_array($m));
        $this->assertTrue(empty($m['excerpt']));
        $this->assertTrue(empty($m['summary']));
    }

    protected function check($file)
    {
        $matter = new FrontMatter(new Resource($file));
        $m = $matter->getMatter();
        $c = $matter->getContents();

        $this->assertTrue(!empty($c));
        $this->assertTrue(!empty($m));
        $this->assertTrue(is_array($m));
        $this->assertTrue(isset($m['excerpt']));
        $this->assertTrue(isset($m['summary']));
        $this->assertTrue(is_array($m['category']));
    }
}

?>
