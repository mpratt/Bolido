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

namespace Bolido\Module\main;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Controller extends \Bolido\App\Adapters\BaseController
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

    /**
     * Installs the framework
     * @return void
     */
    public function BolidoInstall()
    {
        if (!defined('BOLIDO') || BOLIDO !== 'installmode')
            redirectTo($this->app['config']->mainUrl);

        if ($this->session->has('dbpass_tries'))
        {
            $tries = (int) $this->session->get('dbpass_tries');
            if ($tries > 10)
                $this->error->display('Too many tries', 500);

            $this->session->set('dbpass_tries', ($tries+1));
        }
        else
            $this->session->set('dbpass_tries', 1);

        if ($this->input->hasPost('dbpass'))
        {
            $db = $this->config->get('dbInfo');
            if ($this->input->post('dbpass') == $db['pass'])
            {
                $db->query('CREATE TABLE IF NOT EXISTS {dbprefix}error_log (
                            error_id int(10) unsigned NOT NULL AUTO_INCREMENT,
                            message text NOT NULL,
                            backtrace text NOT NULL,
                            ip varbinary(16) NOT NULL,
                            `date` datetime NOT NULL,
                            PRIMARY KEY (error_id),
                            KEY `date` (`date`),
                            KEY ip (ip)
                            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;');

                $db->query('CREATE TABLE IF NOT EXISTS {dbprefix}sessions (
                            session_id varchar(32) NOT NULL,
                            `data` text NOT NULL,
                            last_update int(10) unsigned NOT NULL,
                            UNIQUE KEY session_id (session_id)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

                // Create Cache directory
                if (!is_dir($config->get('cachedir')))
                    mkdir($config->get('cachedir'), 0755);

                // Create Uploads directory
                if (!is_dir($config->get('uploadsdir')))
                    mkdir($config->get('uploadsdir'), 0755);

                $this->template->setHtmlNotification('Installation successful, delete the install.php file', 'success');
            }
            else
                $this->template->setHtmlNotification('Bad Password', 'error');
        }

        $this->template->load('main-install');
    }
}
?>
