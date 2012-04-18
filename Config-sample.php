<?php
/**
 * Config.php
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

final class Config
{
    // Main Configuration
    private $mainurl         = '';
    private $siteTitle       = '';
    private $siteDescription = '';
    private $siteOwner       = '';
    private $masterMail      = '';

    // Database configuration
    private $dbInfo = array('host'   => '',
                            'dbname' => '',
                            'user'   => '',
                            'pass'   => '',
                            'type'     => 'mysql',
                            'charset'  => 'utf8',
                            'dbprefix' => 'bld_');

    // Charset and Language configuration
    private $charset   = 'UTF-8';
    private $language  = 'es';
    private $fallbackLanguage = 'es';
    private $timezone  = 'America/Bogota';
    private $allowedLanguages = array('es');

    // Users Module
    private $usersModule = '';

    // Template configuration
    private $skin = 'default';

    /**
     * Server Options
     * If the serverAutoBalance property is set to true
     * You might need to tweak the serverOverloaded property
     * to suit your needs.
     */
    private $serverAutoBalance = false;
    private $serverOverloaded  = 10;

    /**
     * Other properties that are calculated on construction.
     * !!! You should not touch this !!!
     */
    private $sourcedir;
    private $cachedir;
    private $moduledir;
    private $uploadsdir;
    private $uploadsdirurl;
    private $serverIsUnix;
    private $serverIsWindows;
    private $serverIsMacos;
    private $serverLoad;
    private $httpDomain;

    /**
     * Constructor
     * You dont need to touch this!
     *
     * @return void
     */
    public function __construct()
    {
        // Setup Error Reporting Capabilities
        @ini_set('html_errors', 0);
        if (IN_DEVELOPMENT)
        {
            @ini_set('display_errors', 1);
            error_reporting(E_ALL | E_NOTICE | E_STRICT);
        }
        else
        {
            @ini_set('display_errors', 0);
            error_reporting(E_ALL | ~E_NOTICE);
        }

        // Try to reset useless PHP settings
        @ini_set('register_globals', 0);
        if (function_exists('set_magic_quotes_runtime'))
            @set_magic_quotes_runtime(0);

        // Setup the time zone
        if (function_exists('date_default_timezone_set'))
            date_default_timezone_set($this->timezone);

        // Get Url and Paths
        $this->mainurl = trim($this->mainurl, '/');
        $this->sourcedir = CPATH . '/Bolido/Sources';
        $this->cachedir  = CPATH . '/Bolido/Cache';
        $this->moduledir = CPATH . '/Modules';
        $this->uploadsdir    = CPATH . '/Bolido/Uploads';
        $this->uploadsdirurl = $this->mainurl . '/Bolido/Uploads';

        // Find the domain of the url for session cookie assignment - First make sure the mainurl is not an ip
        $parsedUrl = parse_url($this->mainurl);
        if (!filter_var($parsedUrl['host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            && !filter_var($parsedUrl['host'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {
            if (preg_match('~(?:[^\.]+\.)?([^\.]{2,}\..+)\z~i', $parsedUrl['host'], $parts) == 1)
                $this->httpDomain = $parts[1];
        }

        // Check for some server information
        $this->serverIsUnix    = (bool) (strpos(PHP_OS, 'Linux') !== false || stripos(PHP_OS, 'Unix') !== false);
        $this->serverIsMacos   = (bool) (strpos(PHP_OS, 'Darwin') !== false);
        $this->serverIsWindows = (bool) (strpos(PHP_OS, 'WIN') !== false);

        // Check for server load avarage
        $load = (function_exists('sys_getloadavg') ? sys_getloadavg() : array('0.0001'));
        $this->serverLoad = (float) $load['0'];

        // Register the Autoload function
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Returns the value of the config var
     *
     * @return mixed
     */
    public function get($var)
    {
        if (property_exists($this, $var))
            return $this->$var;

        throw new Exception('Unknown Config Var: ' . $var);
    }

    /**
     * Sets the value of a config
     *
     * @return void
     */
    public function set($var, $value)
    {
        if (isset($this->$var))
            $this->$var = $value;

        return null;
    }

    /**
     * The allmighty autoload function
     * Triggers error, when the class is not found
     *
     * @param string $classname The name of the class that is needed
     * @return bool
     */
    public function autoload($classname)
    {
        if (is_readable($this->get('sourcedir') . '/' . $classname . '.class.php'))
            return require($this->get('sourcedir') . '/' . $classname . '.class.php');
        else if (is_readable($this->get('sourcedir') . '/Interfaces/' . $classname . '.interface.php'))
            return require($this->get('sourcedir') . '/Interfaces/' . $classname . '.interface.php');
        else
            return false;
    }
}
?>
