<?php
/**
 * Autoload.php
 *
 * @package Tests
 * @author Michael Pratt <pratt@hablarmierda.net>
 * @link   http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__ . '/../vendor/autoload.php';

define('SCANNER_DIR', __DIR__ . '/assets/structure');
define('RESOURCE_DIR', __DIR__ . '/assets/resource');
define('WRITABLE_DIR', __DIR__ . '/assets/writable');
define('FRONT_MATTER_DIR', __DIR__ . '/assets/front-matter');

class OutputterMock implements Bolido\Outputter\OutputterInterface
{
    public function write($msg)
    {
        return $msg;
    }
}

