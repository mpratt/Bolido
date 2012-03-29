<?php
/**
 * index.php, Its like a bootstrap!
 * Loads up important files and kicks off the whole process!
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

// Define Important Constants
define('BOLIDO', 1);
define('LOCALMODE', true);
define('IN_DEVELOPMENT', true);
define('BOLIDOVERSION', 0.5);
define('CPATH', dirname(__FILE__));
define('START_TIMER', (float) array_sum(explode(' ', microtime())));

if (LOCALMODE && file_exists(dirname(__FILE__) . '/Config-local.php'))
    require(dirname(__FILE__) . '/Config-local.php');
else
    require(dirname(__FILE__) . '/Config.php');

$config = Config::getInstance();
require($config->get('sourcedir') . '/Main.inc.php');

$dispatcher = new Dispatcher($config);
$dispatcher->loadServices();
$dispatcher->connect($_SERVER['REQUEST_URI']);

?>
