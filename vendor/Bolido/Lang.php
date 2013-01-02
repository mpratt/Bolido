<?php
/**
 * Lang.php, i18n Text Translation
 * Class responsable of loading all the language files
 *
 * @package This file is part of the Bolido Framework
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

        if (strpos($file, '/') === false)
            return false;

        $locations = array();
        list($module, $source) = explode('/', $file, 2);
        if (!empty($module) && !empty($source))
        {
            $locations[] = $this->config->moduleDir . '/' . $module . '/i18n/' . $this->language . '/' . $source . '.lang';
            $locations[] = $this->config->moduleDir . '/' . $module . '/i18n/' . $this->fallbackLanguage . '/' . $source . '.lang';
        }
        $locations[] = $file;

        foreach (array_unique($locations) as $location)
        {
            if (!is_readable($location))
                continue;

            $strings = parse_ini_file($location);
            if (!empty($strings))
            {
                $this->loadedStrings = array_merge($this->loadedStrings, $strings);
                return $this->loadedFiles[$file] = true;
            }
        }

        return false;
    }

    /**
     * Checks if a key is loaded in the language strings
     *
     * @param string $key The key for the language
     * @return bool
     */
    public function exists($key) { return (bool) (isset($this->loadedStrings[$key])); }

    /**
     * Returns a language string, based on the $lang_index
     *
     * @param string $lang_index The key of the string we want to translate
     * @return mixed The translated string
     */
    public function get()
    {
        if (func_num_args() < 1)
            return 'Undefined Lang Index';

        $index = func_get_arg(0);
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
     * Clears the lang strings
     *
     * @return void
     */
    public function free() { $this->loadedStrings = array(); }
}
?>
