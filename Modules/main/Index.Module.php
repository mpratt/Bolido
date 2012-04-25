<?php
/**
 * Index.Module.php, Main Module.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class main extends ModuleAdapter
{
    /**
     * Shows a 404 error!
     * @return void
     */
    public function index()
    {
        $this->error->display('Page not Found', 404);
        die();
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
        if (BOLIDO != 'installmode')
            $this->index();

        if ($this->session->has('dbpass_tries'))
        {
            $tries = (int) $this->session->get('dbpass_tries');
            if ($tries > 10)
            {
                $this->error->display('Too many tries', 500);
                die();
            }

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
                            ip varchar(100) NOT NULL,
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
