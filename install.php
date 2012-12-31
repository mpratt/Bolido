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

// Define Important Constants
define('DEVELOPMENT_MODE', false);

// Start the wiring
require(__DIR__ . '/vendor/Bolido/Bootstrap.php');

$dispatcher = new \Bolido\Dispatcher($deps);
$dispatcher->connect('/main/install/');
?>
