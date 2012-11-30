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

final class Config extends \Bolido\App\Adapters\BaseConfig
{
    // Main Configuration
    private $mainUrl         = '';
    private $siteTitle       = '';
    private $siteDescription = '';
    private $siteOwner       = '';
    private $masterMail      = '';

    // Mysql Database configuration
    private $dbInfo = array('host'   => '',
                            'dbname' => '',
                            'user'   => '',
                            'pass'   => '');

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
     * Other properties that are calculated on Bootstrap.php.
     */
    private $sourceDir;
    private $cacheDir;
    private $moduleDir;
    private $uploadsDir;
    private $uploadsDirUrl;
}

?>
