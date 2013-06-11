<?php
/**
 * IDatabaseHandler.php
 * The interface that defines methods for the database object
 *
 * @package Bolido.Interfaces
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Interfaces;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

interface IDatabaseHandler
{
    /**
     * Connects to a database.
     *
     * @param array $config An associative array with the database configuration
     * @return void
     *
     * @throws PDOException when connection fails.
     */
    public function connect(array $config);

    /**
     * Executes a query and returns its result
     *
     * @param string $query
     * @param array  $values Prepared statement values
     * @return mixed
     */
    public function query($query, $values = array());

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
     * Fetches all the results from a query
     *
     * @return array
     */
    public function fetchAll();

    /**
     * Returns a single column from the result set
     *
     * @param int $column The column you wish to retrieve from the row
     * @return string
     */
    public function fetchColumn($column = 0);

    /**
     * Retrieves a specific row
     *
     * @param int $row Which row to return from
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
     * Returns debug information and stats
     *
     * @return array Associative array with the information
     */
    public function debug();
}
?>
