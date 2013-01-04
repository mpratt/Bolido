<?php
/**
 * Config.php
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

class Config extends \Bolido\Adapters\BaseConfig
{
    public function __construct()
    {
        // Main Configuration
        $this->mainUrl         = '';
        $this->siteTitle       = '';
        $this->siteDescription = '';
        $this->siteOwner       = '';
        $this->masterMail      = '';

        // Mysql Database configuration
        $this->dbInfo = array('type'   => 'mysql',
                              'host'   => '',
                              'dbname' => '',
                              'user'   => '',
                              'pass'   => '');

        // Charset and Language configuration
        $this->charset   = 'UTF-8';
        $this->language  = 'es';
        $this->fallbackLanguage = 'es';
        $this->timezone  = 'America/Bogota';
        $this->allowedLanguages = array('es');

        // Users Module
        $this->usersModule = '';

        // Template configuration
        $this->skin = 'default';
    }
}

?>
