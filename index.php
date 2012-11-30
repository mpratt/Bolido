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
define('DEVELOPMENT_MODE', false);

// Start the wiring
require(__DIR__ . '/Source/Bolido/Bootstrap.php');

$deps = array('config'  => $config
              'session' => $sessio
              'hooks'   => $hooks,
              'error'   => $error,
              'cache'   => $cache,
              'db'      => $db);

$dispatcher = new \Bolido\App\Dispatcher($deps);
$dispatcher->connect($urlParser->getPath());

?>
