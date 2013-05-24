<?php
/**
 * DatabaseHandler.php
 * This class has the responsabilty of making the communication between the App and the database a simple task.
 * Its something like a PDO Wrapper for making things a little easier.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Database implements \Bolido\Interfaces\IDatabaseHandler
{
    protected $pdo;
    protected $stmt;
    protected $config = array();

    // Settings - Do not touch, unless you know what youre doing!
    protected $autocommit = false;
    protected $dbprefix   = 'bld_';

    protected $inTransaction = false;
    protected $rawQuery   = null;
    protected $queries       = 0;
    protected $queryTime     = 0;
    protected $totalTime     = 0;
    protected $insertId      = null;
    protected $affectedRows  = null;

    /**
     * Relevant method documentation is found on the
     * \Bolido\Interfaces\IDatabaseHandler file.
     *
     * This class uses 2 methods that are not defined by the
     * interface. The  __toString and __destruct methods
     * are documented at the end of this file.
     */
    public function __construct(array $config)
    {
        $this->config = array_merge(array(
            'host' => '',
            'dbname' => '',
            'pass' => '',
            'charset' => 'utf8',
        ), $config);
    }

    /**
     * Connects to the Database
     *
     * @return void
     */
    public function connect()
    {
        $dsn = array('mysql' => 'mysql:host=' . $this->config['host'] . ';dbname=' . $this->config['dbname'] . ';charset=' . $this->config['charset'],
                     'pgsql' => 'pgsql:host=' . $this->config['host'] . ';dbname=' . $this->config['dbname'] . ';user=' . $this->config['user'] . ';password=' . $this->config['pass'] . ';charset=' . $this->config['charset']);

        $this->config['type'] = strtolower($this->config['type']);
        if (!isset($dsn[$this->config['type']]))
            throw new \InvalidArgumentException('Unsupported Database Type');

        $this->pdo = new \PDO($dsn[$this->config['type']], $this->config['user'], $this->config['pass']);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('SET NAMES ' . $this->config['charset']);
        $this->pdo->exec('SET sql_mode=\'\'');

        if (!$this->autocommit)
            $this->beginTransaction();
    }

    public function query($query, $values = array())
    {
        if (empty($query))
            return false;

        $this->rawQuery = $query = str_replace(array('{dbprefix}'), $this->dbprefix, $query);
        $this->insertId = $this->affectedRows = 0;
        $startTime = microtime(true);

        // If no values were sent, try to run the query raw
        if (empty($values))
        {
            $this->stmt = $this->pdo->query($query);
            $status = true;
        }
        else
        {
            $this->stmt = $this->pdo->prepare($query);
            $status = $this->stmt->execute((array) $values);
        }

        $this->insertId = $this->pdo->lastInsertId();
        $this->affectedRows = $this->stmt->rowCount();

        $this->queries++;
        $this->queryTime = (microtime(true) - $startTime);
        $this->totalTime += $this->queryTime;

        return $status;
    }

    public function beginTransaction()
    {
        if (!$this->inTransaction)
        {
            $this->inTransaction = true;
            return $this->pdo->beginTransaction();
        }

        return false;
    }

    public function enableAutocommit($bool)
    {
        if ((bool) $bool)
            $this->commit();
        else
            $this->beginTransaction();

        $this->autocommit = $bool;
    }

    public function commit()
    {
        if ($this->inTransaction)
        {
            $this->inTransaction = false;
            return $this->pdo->commit();
        }

        return false;
    }

    public function rollBack()
    {
        if ($this->inTransaction)
        {
            $this->inTransaction = false;
            return $this->pdo->rollBack();
        }

        return false;
    }

    public function fetchAll() { return $this->stmt->fetchAll(\PDO::FETCH_ASSOC); }

    public function fetchColumn($column = 0) { return $this->stmt->fetchColumn($column); }

    public function fetchRow($row = 0) { return $this->stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, $row); }

    public function freeResult()
    {
        $this->stmt = $this->affectedRows = $this->insertId = null;
    }

    public function affectedRows() { return $this->affectedRows; }

    public function insertId() { return $this->insertId; }

    public function quote($string)
    {
        return (string) $this->pdo->quote($string);
    }

    public function runScript($scriptPath = null)
    {
        if (file_exists($scriptPath) && $file = file_get_contents($scriptPath))
        {
            // split the statements! Ignore comments and stuff....
            $statements = preg_split('/;[\n\r]+/', preg_replace('~(?:\-\-.*\n|/\*([^\*]+)\*/)~', '', $file));
            foreach($statements as $query)
                $this->query(trim($query));
        }
        else
            throw new \Exception('Could not read SQL script');
    }

    public function debug()
    {
        return array('queries' => $this->queries,
                     'last_query_time' => $this->queryTime,
                     'last_query' => $this->rawQuery,
                     'total_time' => $this->totalTime,
                     'autocomit'  => $this->autocommit,
                     'last_error' => ($this->stmt === null ? 0 : $this->stmt->errorInfo()),
                     'inTransaction' => $this->inTransaction);
    }

    /**
     * Magic Method used to output minimal debug
     * information.
     *
     * @return string
     */
    public function __toString()
    {
        $debug = $this->debug();
        unset($debug['last_error'], $debug['last_query'], $debug['inTransaction'], $debug['autocomit']);
        return print_r($debug, true);
    }

    /**
     * Destruct
     * This is used to do a last minute commit,
     * before shutting down the request.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->commit();
        $this->freeResult();
    }
}
?>
