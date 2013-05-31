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
        $this->config['mainUrl']    = ''; // The Url for the page
        $this->config['siteTitle']  = ''; // The Title of the Page
        $this->config['siteOwner']  = ''; // Author of the page
        $this->config['masterMail'] = ''; // Email of the Author
        $this->config['siteDescription'] = ''; // Description of the Site

        // Database Configuration
        $this->config['dbInfo'] = array(
            'type'   => 'mysql',
            'host'   => '',
            'dbname' => '',
            'user'   => '',
            'pass'   => ''
        );

        $this->config['language'] = 'es';
        $this->config['cacheMode'] = 'file';

        // Name of the 
        $this->config['usersModule'] = '';
    }
}

?>
