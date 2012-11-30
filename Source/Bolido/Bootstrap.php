<?php
/**
 * Bootstrap.php
 * Does all the app wiring
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

/**
 * Define Important Constants
 */
define('BOLIDO_VERSION', 0.6);

if (!defined('BOLIDO'))
    define('BOLIDO', 1);

if (!defined('DEVELOPMENT_MODE'))
    define('DEVELOPMENT_MODE', false);

if (!defined('BASE_DIR'))
    define('BASE_DIR', realpath(__DIR__ . '/../..'));

if (!defined('SOURCE_DIR'))
    define('SOURCE_DIR', __DIR__);

if (!defined('MODULE_DIR'))
    define('MODULE_DIR', BASE_DIR . '/Modules');

/**
 * If we're on the command line, set the request to use the first argument passed to the script.
 */
if (defined ('STDIN'))
    $_SERVER['REQUEST_URI'] = '/' . (isset($argv['1']) ? ltrim($argv[1], '/') : '');

/**
 * Handle Error reporting levels
 */
@ini_set('html_errors', 0);
@ini_set('display_errors', (int) DEVELOPMENT_MODE);
error_reporting((DEVELOPMENT_MODE ? (E_ALL | E_NOTICE | E_STRICT) : (E_ALL | ~E_NOTICE)));

/**
 * Disable useless PHP settings
 */
@ini_set('register_globals', 0);
if (function_exists('set_magic_quotes_runtime'))
    @set_magic_quotes_runtime(0);

/**
 * Register the magic autoloader
 *
 * Loads files in this format:
 * - new \Bolido\App\Database();
 * - new \Bolido\Module\main\Controller();
 * - new \Bolido\Module\main\model\Hi();
 */
spl_autoload_register(function ($class) {
    $paths = array('\Bolido\App' => SOURCE_DIR, '\Bolido\Module' => MODULE_DIR);
    $class = ltrim(str_replace(array_keys($paths), array_values($paths), $class), '\\');
    $file  = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file))
        require $file;
    else if (file_exists($f = BASE_DIR . DIRECTORY_SEPARATOR . 'Source' . DIRECTORY_SEPARATOR .  $class . '.php'))
        require $f;
});

/**
 * Load important files
 */
require(SOURCE_DIR . '/Functions.php');
require(BASE_DIR . '/Config' . (DEVELOPMENT_MODE && file_exists(BASE_DIR . '/Config-local.php') ? '-local' : '') . '.php');

/**
 * Start Wiring stuff
 */
$config = new \Bolido\Config();
$config->initialize();
date_default_timezone_set($config->timezone);

try {

    $urlParser = new \Bolido\App\UrlParser($_SERVER['REQUEST_URI'], $config);

    // Define the canonical url
    define('CANONICAL_URL', $urlParser->getCanonical());

    if ($urlParser->urlNotConsistent())
        redirectTo(CANONICAL_URL, true);

} catch (\Exception $e) { redirectTo($config->mainUrl . '#invalid-request-uri'); }

// Instantiate Cache object
if (function_exists('apc_cache_info') && function_exists('apc_store'))
    $cache = new \Bolido\App\Cache\ApcEngine();
else
    $cache = new \Bolido\App\Cache\FileEngine($config->cacheDir);

// Load hooks/plugins from all the modules
$hookFiles = $cache->read('hook_files');
if (empty($hookFiles))
{
    $hookFiles = glob(MODULE_DIR . '/*/hooks/*.php');
    if (!empty($hookFiles))
        $cache->store('hook_files', $hookFiles, (15*60));
}

// Instantiate important objects
$session = new \Bolido\App\Session($config->mainUrl);
$hooks   = new \Bolido\App\Hooks($hookFiles);
$error   = new \Bolido\App\ErrorHandler($config, $session, $hooks);

try {
    $db = new \Bolido\App\DatabaseHandler($config->dbInfo);
}
catch(\Exception $e) { $error->display('Error on Database Connection', 503); }

?>
