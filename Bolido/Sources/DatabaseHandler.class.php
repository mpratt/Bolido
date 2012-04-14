<?php
/**
 * DatabaseHandler.class.php
 * This class has the responsabilty of making the communication between the App and the database a simple task.
 * Its something like a PDO Wrapper for making things a little easier.
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

class DatabaseHandler implements iDatabaseHandler
{
    // PDO and PDOStatement Instances
    protected $pdo;
    protected $stmt;

    // Settings - Do not touch, unless you know what youre doing!
    protected $rawQuery   = null;
    protected $autocommit = false;
    protected $debug      = false;
    protected $dbprefix   = 'bld_';
    protected $charset    = 'utf8';
    protected $inTransaction = false;

    // Tracked Data
    protected $queries       = 0;
    protected $queryTime     = 0;
    protected $totalTime     = 0;
    protected $insertId      = null;
    protected $affectedRows  = null;

    /**
     * Construct
     *
     * @param array $config Associative array with credentials and options
     * @return void
     */
    public function __construct($config)
    {
        $this->pdo = new PDO($config['type'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';charset=UTF-8',
                             $config['user'], $config['pass']);

        if (defined('IN_DEVELOPMENT') && IN_DEVELOPMENT)
        {
            $this->debug = true;
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        foreach ($config as $key => $value)
        {
            if ($key != 'pdo' && $key != 'stmt' && property_exists($this, $key))
                $this->$key = $value;
        }

        $this->pdo->exec('SET NAMES ' . $this->charset);
        $this->pdo->exec('SET sql_mode=\'\'');

        if (!$this->autocommit)
            $this->beginTransaction();
    }

    /**
     * Does a smart query, calls prepare() when needed.
     *
     * @param string $query The SQL query
     * @param array $values Array with values for prepared statements
     * @param bool $escapeChars Escape dangerous characters
     * @return bool
     */
    public function query($query, $values = array(), $escapeChars = false)
    {
        if (strpos($query, '{') !== false)
            $query = str_replace(array('{dbprefix}'), $this->dbprefix, $query);

        $this->rawQuery = $query;
        $this->insertId = $this->affectedRows = 0;
        $startTime      = microtime(true);

        // If no values or placeholders were sent, we are probably doing a Select
        if (empty($values) || (strpos($query, '?') === false && strpos($query, ':') === false))
        {
            $this->stmt = $this->pdo->query($query);
            $status = true;
        }
        else
        {
            if (is_string($values))
                $values = array($values);

            if ($escapeChars)
                $values = $this->escapeChars($values);

            $this->stmt = $this->pdo->prepare($query);
            $status = $this->stmt->execute($values);

            if (trim($this->stmt->queryString) != '')
                $this->rawQuery = $this->stmt->queryString;
        }

        $endTime = microtime(true);

        $this->insertId     = $this->pdo->lastInsertId();
        $this->affectedRows = $this->stmt->rowCount();

        $this->queries++;
        $this->queryTime = ($endTime - $startTime);;
        $this->totalTime += $this->queryTime;

        return $status;
    }

    /**
     * Initiates a transaction
     *
     * return bool
     */
    public function beginTransaction()
    {
        $this->inTransaction = true;
        return $this->pdo->beginTransaction();
    }
    public function begin() { return $this->beginTransaction(); }

    /**
     * Enables/Disables Autocommit
     *
     * @param bool $bool true or false
     * @return void
     */
    public function enableAutocommit($bool)
    {
        if ($bool === true)
        {
            //$this->pdo->inTransaction()
            if ($this->inTransaction)
                $this->commit();
        }
        else
        {
            if (!$this->inTransaction)
                $this->beginTransaction();
        }

        $this->autocommit = $bool;
    }

    /**
     * Commits a transaction
     *
     * return bool
     */
    public function commit()
    {
        $this->inTransaction = false;
        return $this->pdo->commit();
    }

    /**
     * Rolls back a transaction
     *
     * return void
     */
    public function rollBack()
    {
        $this->inTransaction = false;
        return $this->pdo->rollBack();
    }

    /**
     * Returns an array containing all of the result set rows
     *
     * @return array
     */
    public function fetchAll() { return $this->stmt->fetchAll(PDO::FETCH_ASSOC); }
    public function fetchAssoc() { return $this->fetchAll(); }

    /**
     * Returns a single column from the next row of a result set
     *
     * @param int $column The column you wish to retrieve from the row
     * @return string
     */
    public function fetchColumn($column = 0) { return $this->stmt->fetchColumn($column); }

    /**
     * Retrieves specific result row
     *
     * @param int $row Which row to return
     * @return array Associative array containing query result row
     */
    public function fetchRow($row = 0) { return $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row); }

    /**
     * Frees the result!
     *
     * @return void
     */
    public function freeResult()
    {
        if (is_object($this->stmt) && method_exists($this->stmt, 'closeCursor'))
            $this->stmt->closeCursor();

        $this->stmt = $this->affectedRows = $this->insertId = null;
    }

    /**
     * Returns number of rows affected by the query
     *
     * @return int
     */
    public function affectedRows() { return $this->affectedRows; }

    /**
     * Returns last insert id, database inserts only
     *
     * @return int
     */
    public function insertId() { return $this->insertId; }

    /**
     * Returns a quoted string that is theoretically safe to pass into an SQL statements
     *
     * @param string $string
     * @return string
     */
    public function quote($string)
    {
        return (string) $this->pdo->quote($string);
    }

    /**
     * Prepares $value for input in a database recursively
     *
     * @param mixed $value
     * @return mixed Clean $value
     */
    protected function escapeChars($value)
    {
        if (!is_array($value))
            return strtr($value, array('%' => '\%', '_' => '\_'));

        foreach ($value as $k => $v)
            $value[$k] = $this->escapeChars($v);

        return $value;
    }

    /**
     * Runs a bunch of sql statements from a file. Great for reading phpmyadmin sql exports
     *
     * @param string $scriptPath The full path to the sql script
     * @return void
     */
    public function runScript($scriptPath = null)
    {
        if ($script = file_get_contents($scriptPath))
        {
            // split the statements! Ignore comments and stuff....
            $statements =  preg_split('/;[\n\r]+/', preg_replace('~(?:\-\-.*\n|/\*([^\*]+)\*/)~', '', $script));
            foreach($statements as $query)
            {
                if (!empty($query))
                    $this->query(trim($query));
            }

            unset($statements, $query, $script);
        }
        else
            throw new Exception('Could not read SQL script');
    }

    /**
     * Shows the last translated query
     *
     * @return string
     */
    public function seeLastQuery() { return $this->rawQuery; }

    /**
     * Shows debug information and stats
     *
     * @return array Associative array with the information
     */
    public function debug()
    {
        return array('queries'    => $this->queries,
                     'last_query_time' => $this->queryTime,
                     'total_time' => $this->totalTime,
                     'autocomit'  => $this->autocommit,
                     'last_error' => ($this->stmt === null ? 0 : $this->stmt->errorInfo()),
                     'inTransaction' => $this->inTransaction);
    }
    public function __toString() { return print_r($this->debug(), true); }

    /**
     * Destructs the object.
     * If we detect a transaction, then do a last commit.
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->inTransaction)
            $this->commit();

        $this->freeResult();
    }
}
?>
