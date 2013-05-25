<?php
/**
 * Controller.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\fake;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Controller
{
    /**
     * A public index method required
     */
    public function index() {}

    /**
     * Throws an exception
     */
    public function throwError() { throw new \Exception('An Exception was thrown'); }

    /**
     * Protected Method
     * @codeCoverageIgnore
     */
    protected function protectedMethod() {}

    /**
     * Private Method
     * @codeCoverageIgnore
     */
    private function privateMethod() {}

    /**
     * Method starting with underscore
     * @codeCoverageIgnore
     */
    public function _underscore() {}

    /**
     * A method with an underscore
     */
    public function method_with_underscore() {}

    /**
     * Required methods
     */
    public function _loadSettings($a) {}
    public function _beforeAction() {}
    public function _flushTemplates() {}
    public function _shutdownModule() {}
}
?>
