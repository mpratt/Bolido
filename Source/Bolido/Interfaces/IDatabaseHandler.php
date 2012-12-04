<?php
/**
 * IDatabaseHandler.php
 * The interface that defines methods for the database object
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\App\Interfaces;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

interface IDatabaseHandler
{
    /**
     * Instantiates the object
     *
     * @param array $config An associative array with the database configuration
     * @return void
     */
    public function __construct(array $config);

    /**
     * Executes a query and returns its result
     *
     * @param string $query
     * @param array  $values Prepared statement values
     * @param bool   $escapeChars
     * @return mixed
     */
    public function query($query, $values = array(), $escapeChars = false);

    /**
     * Starts a transaction in the database
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commits a transaction to the database
     *
     * @return void
     */
    public function commit();

    /**
     * Rolls back a transaction in the database
     *
     * @return void
     */
    public function rollBack();

    /**
     * Fetches an the results of a query
     *
     * @return array
     */
    public function fetchAll();

    /**
     * Returns a single column from the next row of a result set
     *
     * @param int $column The column you wish to retrieve from the row
     * @return string
     */
    public function fetchColumn($column = 0);

    /**
     * Retrieves specific result row
     *
     * @param int $row Which row to return
     * @return array Associative array containing query result row
     */
    public function fetchRow($row = 0);

    /**
     * Frees the result!
     *
     * @return void
     */
    public function freeResult();

    /**
     * Returns number of rows affected by the query
     *
     * @return int
     */
    public function affectedRows();

    /**
     * Returns last insert id, database inserts only
     *
     * @return int
     */
    public function insertId();

    /**
     * Returns a quoted string that is theoretically safe to pass into an SQL statements
     *
     * @param string $string
     * @return string
     */
    public function quote($string);

    /**
     * Runs a bunch of sql statements from a file. Great for reading phpmyadmin sql exports
     *
     * @param string $scriptPath The full path to the sql script
     * @return void
     */
    public function runScript($scriptPath = null);

    /**
     * Returns debug information and stats
     *
     * @return array Associative array with the information
     */
    public function debug();

    /**
     * A magic method that displays some debug information as a string
     *
     * @return string
     */
    public function __toString();
}
?>
