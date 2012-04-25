<?php
/**
 * install.php
 * Bolido Framework Installation script.
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
define('BOLIDO', 'installmode');
define('LOCALMODE', false);
define('IN_DEVELOPMENT', false);
define('BOLIDOVERSION', 0.5);
define('CPATH', dirname(__FILE__));
define('START_TIMER', (float) array_sum(explode(' ', microtime())));

if (LOCALMODE && file_exists(dirname(__FILE__) . '/Config-local.php'))
    require(dirname(__FILE__) . '/Config-local.php');
else
    require(dirname(__FILE__) . '/Config.php');

$config = new BolidoConfig();
require($config->get('sourcedir') . '/Main.inc.php');

$dispatcher = new Dispatcher($config);
$dispatcher->loadServices(false);
$dispatcher->connect('/main/BolidoInstall/');
?>
