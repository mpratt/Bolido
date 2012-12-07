<?php
/**
 * ErrorDBLogger.model.php
 * This class stores errors in the Database
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

class ErrorDBLogger
{
    // Default Session lifetime (48 minutes)
    protected $hooks;
    protected $db;
    protected $session;
    protected $error;

    /**
     * Construct
     *
     * @param object $db
     * @param object $session
     * @return void
     */
    public function init(iDatabaseHandler $db, Session $session, ErrorHandler $error, Hooks $hooks)
    {
        $this->db = $db;

        try
        {
        } catch(Exception $e) {}
    }

    /**
     * Registers the session handler
     *
     * @return void
     */
    public function save($message, $backtrace)
    {
        if (!is_callable(array($this->db, 'query')) || !function_exists('detectIp'))
            return ;

        try {


        } catch(Exception $e) {}
    }
}
?>
