<?php
/**
 * MySQLSessionHandler.php
 * This class manages sessions stored in the Database
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Module\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class MySQLSessionHandler
{
    // Default Session lifetime (48 minutes)
    protected $lifetime = 2880;
    protected $db;
    protected $session;

    /**
     * Construct
     *
     * @param object $db
     * @param object $session
     * @return void
     */
    public function __construct(\Bolido\App\Interfaces\IDatabaseHandler $db, \Bolido\App\Session $sessionHandler)
    {
        $this->db = $db;
        $this->session = $sessionHandler;
    }

    /**
     * Registers the session handler
     *
     * @return void
     */
    public function register()
    {
        if (!$this->session->isStarted())
        {
            if (@ini_get('session.auto_start') == 1 )
                $sessionHandler->close();

            try {
                $this->db->query('SELECT * FROM {dbprefix}sessions');
                session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'),
                                         array(&$this, 'read'), array(&$this, 'write'),
                                         array(&$this, 'destroy'), array(&$this, 'gc'));

            } catch (\Exception $e) {}
        }
    }

    /**
     * Destruct
     * Ensures that app writes down all session data
     *
     * @return void
     */
    public function __destruct()
    {
        session_write_close();
        $this->db->__destruct();
    }

    /**
     * Opens the session data. It has no use, since we dont have to open files
     *
     * @param string $savePath
     * @param string $sessionName
     * @return bool always true
     */
    public function open($savePath, $sessionName) { return true; }

    /**
     * Reads the session data from db
     *
     * @param string $id Session Id
     * @return mixed session data
     */
    public function read($id)
    {
        $this->db->query('SELECT data FROM {dbprefix}sessions
                          WHERE session_id = ?', array($id));

        $data = $this->db->fetchColumn();
        $this->db->freeResult();

        return $data;
    }

    /**
     * Writes the session data to db
     *
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        if (preg_match('~^[A-Za-z0-9]{16,32}$~', $id) == 0)
            return false;

        // First try to update an existing row...
        $this->db->query('UPDATE {dbprefix}sessions
                          SET data = ?, last_update = ?
                          WHERE session_id = ?', array($data, time(), $id));

        // If that didn't work, try inserting a new one.
        if ($this->db->affectedRows() == 0)
        {
            try
            {
                $this->db->query('INSERT IGNORE INTO {dbprefix}sessions (session_id, data, last_update)
                                  VALUES(?, ?, ?)', array($id, $data, time()));
            }
            catch(Exception $e) { return false; }
        }

        return true;
    }

    /**
     * Closes a session file. Unneeded
     *
     * @return bool always true
     */
    public function close() { return true; }

    /**
     * Destroys a session
     *
     * @param string $id
     * @return bool true if the session was destroyed. False otherwise
     */
    public function destroy($id)
    {
        if (preg_match('~^[A-Za-z0-9]{16,32}$~', $id) == 0)
            return false;

        return $this->db->query('DELETE FROM {dbprefix}sessions
                                 WHERE session_id = ?', array($id));
    }

    /**
     * Garbage collector
     *
     * @return bool true if the query was succesfull. False otherwise
     */
    public function gc()
    {
        return $this->db->query('DELETE FROM {dbprefix}sessions
                                 WHERE last_update < ?', array((time() - $this->lifetime)));
    }
}
?>
