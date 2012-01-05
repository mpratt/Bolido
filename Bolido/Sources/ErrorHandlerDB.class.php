<?php
/**
 * ErrorHandlerDB.class.php
 * Logs errors in the Database
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class ErrorHandlerDB
{
    protected $db;

    /**
     * Construct
     *
     * @param object $db
     * @return void
     */
    public function __construct(iDatabaseHandler $db) { $this->db = $db; }

    /**
     * Logs a error to the database
     *
     * @param string $message
     * @param string $backtrace
     * @return void
     */
    public function log($message, $backtrace)
    {
        $this->db->query('INSERT INTO {dbprefix}error_log
                          (message, backtrace, ip, date) VALUES (?, ?, ?, ?)', array($message,
                                                                                     $backtrace,
                                                                                     detectIp(),
                                                                                     date('Y-m-d H:i:s')));
    }
}
?>