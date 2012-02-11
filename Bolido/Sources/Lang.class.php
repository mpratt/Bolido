<?php
/**
 * Lang.class.php, i18n Text Translation
 * Class responsable of loading all the language files
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

class Lang
{
    protected $config;
    protected $hooks;
    protected $moduleContext;
    protected $loadedStrings = array();
    protected $loadedFiles   = array();

    /**
     * Construct
     *
     * @param object $config
     * @param object $hooks
     * @param string $moduleContext The Current Module
     * @return void
     */
    public function __construct(Config $config, Hooks $hooks, $moduleContext = 'main')
    {
        $this->config = $config;
        $this->hooks  = $hooks;
        $this->moduleContext = $moduleContext;

        $autoload = $this->hooks->run('load_langs', array());
        if (!empty($autoload) && is_array($autoload))
        {
            foreach ($autoload as $lang)
                $this->load($lang);
        }
    }

    /**
     * Loads a language file. It looks for it in different places until it finds it
     * If the language file was not found, it dies!
     *
     * @param string $file The name of the language file with or without the full path
     * @return void
     */
    public function load($file)
    {
        if (isset($this->loadedFiles[$file]))
            return ;

        $locations = array($this->config->get('moduledir') . '/' . $this->moduleContext . '/i18n/' . basename($file));

        // Loading the language from another module context? As in users/users_main
        if (strpos($file, '/') !== false)
        {
            $parts = explode('/', strtolower($file), 2);
            if (count($parts) > 0)
                $locations[] = $this->config->get('moduledir') . '/' . $parts['0'] . '/i18n/' . $parts['1'];

            $locations[] = $file;
        }

        $strings = array();
        foreach ($locations as $location)
        {
            if (is_readable($location . '.' . $this->config->get('language') . '.lang'))
                $strings = parse_ini_file($location . '.' . $this->config->get('language') . '.lang');
            else if (is_readable($location . '.' . $this->config->get('fallbackLanguage') . '.lang'))
                $strings = parse_ini_file($location . '.' . $this->config->get('fallbackLanguage') . '.lang');

            if (!empty($strings))
            {
                $this->loadedStrings = array_merge($this->loadedStrings, $strings);
                break;
            }
        }

        $this->loadedFiles[$file] = 'loaded';
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
    public function __destruct() { $this->free(); }
}
?>