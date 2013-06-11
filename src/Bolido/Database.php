<?php
/**
 * DatabaseHandler.php
 * This class has the responsabilty of making the communication between the App and the database a simple task.
 * Its something like a PDO Wrapper that make things easier.
 *
 * @package Bolido
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

    protected $rawQuery = null;
    protected $queries  = 0;
    protected $queryTime = 0;
    protected $totalTime = 0;
    protected $insertId  = null;
    protected $affectedRows  = null;
    protected $inTransaction = 0;

    /**
     * {@inheritdoc }
     */
    public function connect(array $config)
    {
        $this->config = array_merge(array(
            'charset' => 'utf8',
            'autocommit' => false,
            'dbprefix' => 'bld_',
            'host' => '',
            'dbname' => '',
            'pass' => '',
        ), $config);

        $this->config['type'] = strtolower($this->config['type']);
        if (!in_array($this->config['type'], array('mysql', 'pgsql')))
            throw new \InvalidArgumentException('Unsupported Database Type');

        $dsn = $this->config['type'] . ':host=' . $this->config['host'] . ';dbname=' . $this->config['dbname'] . ';charset=' . $this->config['charset'];
        $options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->config['charset'] . ',sql_mode=\'\'');

        $this->pdo = new \PDO($dsn, $this->config['user'], $this->config['pass'], $options);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        if (!$this->config['autocommit'])
            $this->beginTransaction();
    }

    /**
     * Enables/Disables autocommit
     *
     * @param bool $bool
     * @return void
     */
    public function enableAutocommit($bool)
    {
        if ((bool) $bool)
            $this->commit();
        else
            $this->beginTransaction();

        $this->config['autocommit'] = $bool;
    }

    /**
     * {@inheritdoc }
     */
    public function query($query, $values = array())
    {
        $this->rawQuery = $query = str_replace(array('{dbprefix}'), $this->config['dbprefix'], $query);
        $this->insertId = $this->affectedRows = 0;
        $startTime = microtime(true);

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

    /**
     * {@inheritdoc }
     */
    public function beginTransaction()
    {
        $this->inTransaction += 1;
        return $this->pdo->beginTransaction();
    }

    /**
     * {@inheritdoc }
     */
    public function commit()
    {
        if ($this->inTransaction > 0)
        {
            $this->inTransaction -= 1;
            return $this->pdo->commit();
        }

        return false;
    }

    /**
     * {@inheritdoc }
     */
    public function rollBack()
    {
        if ($this->inTransaction > 0)
        {
            $this->inTransaction -= 1;
            return $this->pdo->rollBack();
        }

        return false;
    }

    /**
     * {@inheritdoc }
     */
    public function fetchAll() { return $this->stmt->fetchAll(\PDO::FETCH_ASSOC); }

    /**
     * {@inheritdoc }
     */
    public function fetchColumn($column = 0) { return $this->stmt->fetchColumn($column); }

    /**
     * {@inheritdoc }
     */
    public function fetchRow($row = 0) { return $this->stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, $row); }

    /**
     * {@inheritdoc }
     */
    public function freeResult() { $this->stmt = $this->affectedRows = $this->insertId = null; }

    /**
     * {@inheritdoc }
     */
    public function affectedRows() { return $this->affectedRows; }

    /**
     * {@inheritdoc }
     */
    public function insertId() { return $this->insertId; }

    /**
     * {@inheritdoc }
     */
    public function quote($string) { return (string) $this->pdo->quote($string); }

    /**
     * {@inheritdoc }
     */
    public function debug()
    {
        return array('queries' => $this->queries,
                     'last_query_time' => $this->queryTime,
                     'last_query' => $this->rawQuery,
                     'total_time' => $this->totalTime,
                     'autocomit'  => $this->config['autocommit'],
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
     * This method is used to do a last minute pending commit,
     * before shutting down the request.
     *
     * @return void
     */
    public function __destruct()
    {
        try {
            do { $this->commit(); } while ($this->inTransaction > 0);
        } catch(\Exception $e) {}

        $this->freeResult();
    }
}
?>
