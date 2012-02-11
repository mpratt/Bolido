<?php
/**
 * install.php, Its like a bootstrap!
 * Loads up important files and kicks off the whole process!
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
    define('BOLIDO', 'install');
    require_once(dirname(__FILE__) . '/Config.php');
    $config = Config::getInstance();
    error_reporting(E_ALL | E_NOTICE | E_STRICT);

    // Include main functions
    require($config->get('sourcedir') . '/Main.inc.php');
    $info = $config->get('dbInfo');

    try    {
        $db = new DatabaseHandler($info);
    }
    catch(Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        die('Error on Database connection :(');
    }

    if (empty($_POST['dbpass']))
    {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta http-equiv="Author" content="Michael Pratt" />
                <title>Write Database Password</title>
            </head>
            <body>
                <form action="install.php?' . time() . '" method="post" accept-charset="UTF-8">
                    <label>Database Password</label>
                    <input name="dbpass" type="password" />
                    <input value="go" type="submit" />
                </form>
            </body>
        </html>';

        die();
    }
    else if ($info['pass'] != $_POST['dbpass'])
        redirectTo('install.php');

    // Check if framework is already installed
    $db->query('SELECT COUNT(*)
                FROM information_schema.tables
                WHERE table_schema = ?
                AND table_name IN (\'{dbprefix}error_log\', \'{dbprefix}sessions\')', $info['dbname']);

    $tables = (int) $db->fetchColumn();

    // Create needed database tables if needed
    if ($tables != 2)
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
    }

    // Create Cache directory
    if (!is_dir($config->get('cachedir')) && !mkdir($config->get('cachedir'), 0755))
        die('Could not create cache directory - Check that ' . dirname($config->get('cachedir')) . ' is writable');

    // Create Uploads directory
    if (!is_dir($config->get('uploadsdir')) && !mkdir($config->get('uploadsdir'), 0755))
        die('Could not create uploads directory - Check that ' . dirname($config->get('uploadsdir')) . ' is writable');

    redirectTo($config->get('mainurl'));
?>