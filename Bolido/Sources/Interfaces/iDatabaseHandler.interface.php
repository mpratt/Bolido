<?php
/**
 * iDatabaseHandler.class.php
 * The interface that defines methods for the database object
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

interface iDatabaseHandler
{
    public function __construct($config);
    public function query($query, $values = array(), $escapeChars = false);
    public function beginTransaction();
    public function commit();
    public function rollBack();
    public function fetchAll();
    public function fetchAssoc();
    public function fetchColumn($column = 0);
    public function fetchRow($row = 0);
    public function freeResult();
    public function affectedRows();
    public function insertId();
    public function runScript($scriptPath = null);
    public function debug();
    public function __toString();
    public function __destruct();
}
?>
