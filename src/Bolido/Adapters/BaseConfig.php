<?php
/**
 * BaseConfig.php
 * This is the adapter that should be used by the Config class
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
    public $mainUrl, $siteTitle, $siteDescription, $siteOwner, $masterMail, $dbInfo;
    public $charset, $language, $fallbackLanguage, $allowedLanguages, $timezone;
    public $usersModule, $skin;
    public $cacheMode;
    public $sourceDir, $logDir, $cacheDir, $moduleDir, $uploadsDir, $uploadsDirUrl;

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

        if (empty($this->timezone))
            $this->timezone = 'America/Bogota';

        if (empty($this->language))
            $this->language = 'en';

        if (empty($this->fallbackLanguage))
            $this->fallbackLanguage = $this->language;

        $this->allowedLanguages = array_unique(array_merge((array) $this->allowedLanguages, array($this->language, $this->fallbackLanguage)));

        if (empty($this->usersModule))
            $this->usersModule = '\Bolido\Modules\main\models\MainUserModule';

        if (empty($this->skin))
            $this->skin = 'default';

        if (empty($this->cacheMode) || !in_array(strtolower($this->cacheMode), array('file', 'apc')))
            $this->cacheMode = 'file';
        else
            $this->cacheMode = strtolower($this->cacheMode);
    }

    /**
     * Returns the value of the config var
     *
     * @param mixed $property
     * @return mixed
     *
     * @throws InvalidArgumentException when a property doesnt exists.
     */
    public function __get($property)
    {
        if (property_exists($this, $property))
            return $this->$property;

        throw new \InvalidArgumentException('Unknown Config Property: ' . $property);
    }

    /**
     * Sets the value of a config
     *
     * @param string $property
     * @param mixed  $value
     * @return void
     */
    public function __set($property, $value) { $this->$property = $value; }
}

?>
