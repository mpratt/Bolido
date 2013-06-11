<?php
/**
 * Lang.php
 * i18n Text Translation class that is responsable of
 * loading all the language files
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Lang
{
    protected $config;
    protected $language;
    protected $fallbackLanguage;
    protected $loadedStrings = array();
    protected $loadedFiles   = array();

    /**
     * Construct
     *
     * @param object $config
     * @return void
     */
    public function __construct(\Bolido\Adapters\BaseConfig $config)
    {
        if (isset($_GET['locale']) && in_array($_GET['locale'], $config->allowedLanguages))
            $this->language = $_GET['locale'];
        else
            $this->language = $config->language;

        $this->fallbackLanguage = $config->fallbackLanguage;
        $this->config = $config;
    }

    /**
     * Returns the currently used language
     *
     * @return string
     */
    public function getCurrentLanguage() { return $this->language; }

    /**
     * Loads a language file. It looks for it in different places until it finds it
     *
     * @param string $file The name of the language file with the full path or something like users/user_main
     * @return bool
     */
    public function load($file)
    {
        if (isset($this->loadedFiles[$file]))
            return true;

        if (strpos($file, '/') !== false)
        {
            list($module, $source) = explode('/', $file, 2);
            $locations = array($this->config->moduleDir . '/' . $module . '/i18n/' . $this->language . '/' . $source . '.lang',
                               $this->config->moduleDir . '/' . $module . '/i18n/' . $this->fallbackLanguage . '/' . $source . '.lang',
                               $file);

            foreach (array_unique($locations) as $location)
            {
                if (is_readable($location))
                {
                    $this->loadedStrings = array_merge($this->loadedStrings, (array) @parse_ini_file($location));
                    return $this->loadedFiles[$file] = true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if a key is loaded in the language strings
     *
     * @param string $key
     * @return bool
     */
    public function exists($key) { return (isset($this->loadedStrings[$key])); }

    /**
     * Returns a translated language string, based on the
     * $lang index.
     *
     * @param mixed Params are determined by the func_get_args() function.
     * @return string
     */
    public function get()
    {
        if (func_num_args() < 1)
            return 'Undefined Lang Index';

        $index = func_get_arg(0);
        if (is_array($index) && !empty($index))
            return call_user_func_array(array($this, 'get'), $index);

        if (isset($this->loadedStrings[$index]))
        {
            if (func_num_args() > 1)
            {
                $params = func_get_args();
                array_shift($params);
                return vsprintf($this->loadedStrings[$index], $params);
            }
            else
                return $this->loadedStrings[$index];
        }

        return $index;
    }

    /**
     * Clears loaded strings
     *
     * @return void
     */
    public function free() { $this->loadedStrings = array(); }
}
?>
