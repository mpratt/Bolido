<?php
/**
 * Controller.php
 * The Controller of the Main Module.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Controller extends \Bolido\Adapters\BaseController
{
    /**
     * Shows the welcome page.
     * @return void
     */
    public function index()
    {
        $checks = array();
        $checks['php_version'] = (bool) (version_compare(phpversion(), '5.4' , '>='));
        $checks['cache_dir'  ] = (bool) (is_writable($this->app['config']->cacheDir));
        $checks['uploads_dir'] = (bool) (is_writable($this->app['config']->uploadsDir));
        $checks['logs_dir'] = (bool) (is_writable($this->app['config']->logsDir));
        $checks['ext_pdo']  = (bool) (extension_loaded('PDO'));
        $checks['ext_spl']  = (bool) (extension_loaded('SPL'));
        $checks['ext_gd']   = (bool) (extension_loaded('gd'));
        $checks['ext_reflection'] = (bool) (extension_loaded('Reflection'));

        $this->app['lang']->load('main/main');
        $this->app['template']->setHtmlTitle($this->app['lang']->get('main_welcome_title'));
        $this->app['template']->allowHtmlIndexing(false);
        $this->app['template']->load('main/main-welcome', array('checks' => $checks));
    }

    /**
     * Keeps Session Alive.
     * @return void
     */
    public function alive()
    {
        header('Cache-Control: no-cache');
        header('Content-type: image/gif');
        $img = imagecreate(1, 1);
        imagegif($img);
        imagedestroy($img);
        die();
    }
}
?>
