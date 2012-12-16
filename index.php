<?php
/**
 * index.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

// Define Important Constants
define('BOLIDO', 1);
define('DEVELOPMENT_MODE', true);

// Start the wiring
require(__DIR__ . '/Source/Bolido/Bootstrap.php');

$dispatcher = new \Bolido\App\Dispatcher($app);
$dispatcher->connect($urlParser->getPath());

?>
