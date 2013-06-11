<?php
/**
 * Bootstrap.php
 * Does all the app wiring
 *
 * @package Bolido
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
define('BOLIDO_VERSION', '0.8.0');

if (!defined('BOLIDO'))
    define('BOLIDO', 1);

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

if (!defined('DEVELOPMENT_MODE'))
    define('DEVELOPMENT_MODE', false);

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
 * Register Composer's Autoloader
 */
require BASE_DIR . '/vendor/autoload.php';

/**
 * Load Important Files.
 */
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
 * Initialize the Configuration object
 */
$config = new \Bolido\Config();
$config->initialize();
date_default_timezone_set($config->timezone);

$app = new \Bolido\Container($config, $benchmark);

try {
    define('CANONICAL_URL', $app['urlparser']->getCanonical());
    if ($app['urlparser']->urlNotConsistent())
        redirectTo(CANONICAL_URL, true);
} catch (\Exception $e) { redirectTo($config->mainUrl . '/#invalid-request-uri'); }

/**
 * Source custom modifications
 */
if (file_exists(BASE_DIR . '/CustomBootstrap.php'))
    require BASE_DIR . '/CustomBootstrap.php';
?>
