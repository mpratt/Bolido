<?php
/**
 * TestTemplateMinifier.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
use \Bolido\Modules\main\models\TemplateMinifier as Minifier;

require_once(BASE_DIR . '/../modules/main/models/TemplateMinifier.php');
class TestTemplateMinifier extends PHPUnit_Framework_TestCase
{
    /**
     * Basic Minifier tests
     */
    public function testMinifier()
    {
        $m = new Minifier();
        $this->assertEquals($m->html('<div>  Hello Friends  </div>'), '<div> Hello Friends</div>');
        $this->assertEquals($m->html('<div   class="hi">     Ho     </div>'), '<div   class="hi"> Ho</div>');
        $this->assertEquals($m->html('<pre>   $hi = bu; </pre>'), '<pre>   $hi = bu; </pre>');
        $this->assertEquals($m->html('<textarea>   $hi = bu; </textarea>'), '<textarea>   $hi = bu; </textarea>');
        $this->assertEquals($m->html('   <div>   hola</div>'), '<div> hola</div>');
        $this->assertEquals($m->html('<div>Hola</div>   <br />   <p>   hola</p>'), '<div>Hola</div> <br /><p> hola</p>');
    }
}
?>
