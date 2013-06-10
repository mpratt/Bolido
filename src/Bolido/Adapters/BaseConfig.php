<?php
/**
 * BaseConfig.php
 * This is the adapter that should be used by the Config class
 *
 * @package Bolido.Adapters
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
    protected $defaultValues = array();
    protected $config = array();

    /**
     * Initializes important or missing data
     *
     * @return void
     */
    public function initialize()
    {
        if (empty($this->config['mainUrl']))
            throw new \InvalidArgumentException('You need to define the mainUrl in your configuration');

        $this->config['mainUrl'] = trim($this->config['mainUrl'], '/');
        $this->defaultValues = array(
            'sourceDir' => SOURCE_DIR,
            'cacheDir' => CACHE_DIR,
            'moduleDir' => MODULE_DIR,
            'logsDir' => LOGS_DIR,
            'uploadsDir' => BASE_DIR . '/assets/Uploads',
            'uploadsDirUrl' => $this->config['mainUrl'] . '/assets/Uploads',
            'charset' => 'UTF-8',
            'timezone' => 'UTC',
            'language' => 'en',
            'allowedLanguages' => array(),
            'usersModule' => '\Bolido\Modules\main\models\MainUserModule',
            'skin' => 'default',
            'cacheMode' => 'file',
            'dbInfo' => array()
        );

        $this->config = array_merge($this->defaultValues, $this->config);
        if (empty($this->config['fallbackLanguage']))
            $this->config['fallbackLanguage'] = $this->config['language'];

        $this->config['allowedLanguages'] = array_unique(array_merge(
            $this->config['allowedLanguages'],
            array($this->config['language'], $this->config['fallbackLanguage'])
        ));

        $this->config['cacheMode'] = strtolower($this->config['cacheMode']);
        if (!in_array($this->config['cacheMode'], array('file', 'apc')))
            $this->config['cacheMode'] = 'file';
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
        if (isset($this->config[$property]))
            return $this->config[$property];

        throw new \InvalidArgumentException('Unknown Config Property: ' . $property);
    }

    /**
     * Checks if a property exists
     *
     * @param mixed $property
     * @return bool
     */
    public function __isset($property) { return isset($this->config[$property]); }

    /**
     * Sets the value of a config
     *
     * @param string $property
     * @param mixed  $value
     * @return void
     */
    public function __set($property, $value) { $this->config[$property] = $value; }
}

?>
