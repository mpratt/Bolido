<?php
/**
 * BaseConfig.php
 * This is the adapter that should be used by the Configuration Class
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Adapters;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

abstract class BaseConfig
{
    private $mainUrl, $siteTitle, $siteDescription, $siteOwner, $masterMail, $dbInfo;
    private $charset, $language, $fallbackLanguage, $allowedLanguages, $timezone;
    private $usersModule, $skin;
    private $sourceDir, $logDir, $cacheDir, $moduleDir, $uploadsDir, $uploadsDirUrl;

    /**
     * Initializes important or missing data
     *
     * @return void
     */
    public function initialize()
    {
        $this->mainUrl = trim($this->mainUrl, '/');
        $this->sourceDir = SOURCE_DIR;
        $this->cacheDir  = CACHE_DIR;
        $this->moduleDir = MODULE_DIR;
        $this->logsDir   = LOGS_DIR;

        if (empty($this->uploadsDir))
            $this->uploadsDir = BASE_DIR . '/assets/Uploads';

        if (empty($this->uploadsDirUrl))
            $this->uploadsDirUrl = $this->mainUrl . '/assets/Uploads';

        if (empty($this->dbInfo) || !is_array($this->dbInfo))
            $this->dbInfo = array();

        if (empty($this->timezone))
            $this->timezone = 'America/Bogota';

        if (empty($this->language))
            $this->language = 'en';

        if (empty($this->fallbackLanguage))
            $this->fallbackLanguage = $this->language;

        if (empty($this->allowedLanguages))
            $this->allowedLanguages = array_unique(array($this->language, $this->fallbackLanguage));
        else
            $this->allowedLanguages = array_unique(array_merge($this->allowedLanguages, array($this->language, $this->fallbackLanguage)));

        if (empty($this->usersModule))
            $this->usersModule = '\Bolido\Modules\main\models\MainUserModule';

        if (empty($this->skin))
            $this->skin = 'default';
    }

    /**
     * Returns the value of the config var
     *
     * @return mixed
     */
    public function __get($var)
    {
        if (property_exists($this, $var))
            return $this->$var;

        throw new \InvalidArgumentException('Unknown Config Property: ' . $var);
    }

    /**
     * Sets the value of a config
     *
     * @return void
     */
    public function __set($var, $value) { $this->$var = $value; }
}

?>
