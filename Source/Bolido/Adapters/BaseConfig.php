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

namespace Bolido\App\Adapters;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

abstract class BaseConfig
{
    private $mainUrl, $siteTitle, $siteDescription, $siteOwner, $masterMail, $dbInfo;
    private $charset, $language, $allowedLanguages, $timezone;
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
        if (empty($this->sourceDir))
            $this->sourceDir = BASE_DIR . '/Source/Bolido';

        if (empty($this->cacheDir))
            $this->cacheDir = BASE_DIR . '/Cache';

        if (empty($this->moduleDir))
            $this->moduleDir = BASE_DIR . '/Modules';

        if (empty($this->logDir))
            $this->logDir = BASE_DIR . '/Logs';

        if (empty($this->uploadsDir))
            $this->uploadsDir = BASE_DIR . '/Uploads';

        if (empty($this->uploadsDirUrl))
            $this->uploadsDirUrl = $this->mainUrl . '/Uploads';

        if (empty($this->dbInfo) || !is_array($this->dbInfo))
            $this->dbInfo = array();

        if (empty($this->timezone))
            $this->timezone = 'America/Bogota';

        if (empty($this->language))
            $this->language = 'en_US';

        if (empty($this->fallbackLanguage))
            $this->fallbackLanguage = $this->language;

        if (empty($this->allowedLanguages))
            $this->allowedLanguages = array($this->language);

        if (empty($this->usersModule))
            $this->usersModule = '\Bolido\Modules\Main\DummyUser';

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

        throw new \Exception('Unknown Config Property: ' . $var);
    }

    /**
     * Sets the value of a config
     *
     * @return void
     */
    public function __set($var, $value) { $this->$var = $value; }
}

?>
