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
define('BOLIDO_VERSION', '0.7.1');

if (!defined('BOLIDO'))
    define('BOLIDO', 1);

if (!defined('DEVELOPMENT_MODE'))
    define('DEVELOPMENT_MODE', false);

if (!defined('BASE_DIR'))
    define('BASE_DIR', realpath(__DIR__ . '/../..'));

if (!defined('SOURCE_DIR'))
    define('SOURCE_DIR', __DIR__);

if (!defined('MODULE_DIR'))
    define('MODULE_DIR', BASE_DIR . '/modules');

if (!defined('ASSETS_DIR'))
    define('ASSETS_DIR', BASE_DIR . '/assets');

if (!defined('CACHE_DIR'))
    define('CACHE_DIR', ASSETS_DIR . '/Cache');

if (!defined('LOGS_DIR'))
    define('LOGS_DIR', ASSETS_DIR . '/Logs');

/**
 * Register the magic autoloader
 *
 * Loads files in this format:
 * - new \Bolido\Database();
 * - new \Bolido\Modules\main\Controller();
 * - new \Bolido\Modules\main\models\Hi();
 */
spl_autoload_register(function ($class) {
    if (stripos($class, 'bolido') === false)
        return ;

    $class = str_replace('\\', DIRECTORY_SEPARATOR, ltrim($class, '\\'));
    if (strpos($class, 'Bolido' . DIRECTORY_SEPARATOR . 'Modules') !== false)
    {
        $class = str_replace('Bolido' . DIRECTORY_SEPARATOR . 'Modules', MODULE_DIR, $class) . '.php';
        if (file_exists($class))
            require $class;
    }
    else if (file_exists($f = BASE_DIR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class . '.php'))
        require $f;
});

/**
 * Start the Benchmarking process
 */
$benchmark = new \Bolido\Benchmark();
$benchmark->startTimerTracker('Bootstrap-start');

/**
 * Support for composer autoloader
 * Load Important Files.
 */
if (file_exists(BASE_DIR . '/vendor/autoload.php'))
    require BASE_DIR . '/vendor/autoload.php';

require SOURCE_DIR . '/Functions.php';
require BASE_DIR . '/Config' . (DEVELOPMENT_MODE && file_exists(BASE_DIR . '/Config-local.php') ? '-local' : '') . '.php';

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
 * Start Wiring stuff
 */
$config = new \Bolido\Config();
$config->initialize();
date_default_timezone_set($config->timezone);

try {

    $urlParser = new \Bolido\UrlParser($_SERVER['REQUEST_URI'], $config);

    // Define the canonical url
    define('CANONICAL_URL', $urlParser->getCanonical());

    if ($urlParser->urlNotConsistent())
        redirectTo(CANONICAL_URL, true);

} catch (\Exception $e) { redirectTo($config->mainUrl . '#invalid-request-uri'); }

// Instantiate Cache object
if ($config->cacheMode == 'apc' && function_exists('apc_store'))
    $cache = new \Bolido\Cache\ApcEngine($config->mainUrl);
else
    $cache = new \Bolido\Cache\FileEngine($config->cacheDir);

// Load hooks/plugins from all the modules
$hookFiles = $cache->read('hook_files');
if (empty($hookFiles))
{
    $hookFiles = glob($config->moduleDir . '/*/hooks/*.php');
    if (!empty($hookFiles))
        $cache->store('hook_files', $hookFiles, (15*60));
}

// Instantiate important objects
$session = new \Bolido\Session($config->mainUrl);
$hooks = new \Bolido\Hooks($hookFiles);
$lang  = $hooks->run('modify_lang', new \Bolido\Lang($config));

// Instantiate the remaining objects
$template = $hooks->run('extend_template', new \Bolido\Template($config, $lang, $session, $hooks));

$error  = new \Bolido\ErrorHandler($hooks, $template);
$error->register();

$router = $hooks->run('modify_router', new \Bolido\Router($_SERVER['REQUEST_METHOD']));

// Instantiate the database
try {
    $db = new \Bolido\Database($config->dbInfo);
}
catch(\Exception $e) { $error->display('Error on Database Connection', 503); }

// Instantiate the app registry
$app = new \Bolido\AppRegistry();
$app['config']   = $config;
$app['session']  = $session;
$app['cache']    = $cache;
$app['hooks']    = $hooks;
$app['lang']     = $lang;
$app['error']    = $error;
$app['db']       = $db;
$app['router']   = $router;
$app['template'] = $template;
$app['benchmark'] = $benchmark;
$app['hooks']->run('extend_app_registry', $app);

/**
 * Source custom modifications
 */
if (file_exists(BASE_DIR . '/CustomBootstrap.php'))
    require BASE_DIR . '/CustomBootstrap.php';
?>
