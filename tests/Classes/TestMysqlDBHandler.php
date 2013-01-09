<?php
/**
 * TestDBHandler.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

class TestDBHandler extends PHPUnit_Framework_TestCase
{
    protected $db;

    /**
     * Setup the environment
     */
    public function setUp()
    {
        if (!file_exists(__DIR__ . '/../Workspace/db.php'))
        {
            $this->markTestSkipped('No DB credentials was found');
            return ;
        }

        try
        {
            require(__DIR__ . '/../Workspace/db.php');
            $this->db = new \Bolido\Database($dbConfig);
            $this->db->enableAutocommit(true);
            $this->db->query('CREATE TABLE IF NOT EXISTS {dbprefix}bolido_tests (
                                `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                `name` varchar(80) NOT NULL DEFAULT \'\',
                                `password` varchar(64) NOT NULL DEFAULT \'\',
                                `email` varchar(255) NOT NULL DEFAULT \'\',
                                PRIMARY KEY (`user_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;');

            $this->db->query('INSERT INTO {dbprefix}bolido_tests (name, password, email) VALUES (?, ?, ?)', array('1', '2', '3'));
            $this->db->query('INSERT INTO {dbprefix}bolido_tests (name, password, email) VALUES (?, ?, ?)', array('4', '5', '6'));
            $this->db->query('INSERT INTO {dbprefix}bolido_tests (name, password, email) VALUES (?, ?, ?)', array('7', '8', '9'));
            $this->db->query('INSERT INTO {dbprefix}bolido_tests (name, password, email) VALUES (?, ?, ?)', array('10', '11', '12'));
        }
        catch(Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
    }

    /**
     * Cleanup the environment after testing
     */
    public function tearDown()
    {
        if (!is_object($this->db))
            return ;

        $this->db->query('DROP TABLE IF EXISTS {dbprefix}bolido_tests;');
    }

    /**
     * Test InsertId
     */
    public function testInsertId()
    {
        $this->db->query('INSERT INTO {dbprefix}bolido_tests (name, password, email) VALUES (?, ?, ?)', array('uno', 'dos', 'tres'));
        $this->assertEquals($this->db->insertId(), 5);

        $this->db->query('INSERT INTO {dbprefix}bolido_tests (name) VALUES (?)', 'mike');
        $this->assertEquals($this->db->insertId(), 6);

        $this->db->query('INSERT INTO {dbprefix}bolido_tests (name, password, email) VALUES (?, ?, ?)', array('cuatro', 'cinco', 'seis'));
        $this->assertEquals($this->db->insertId(), 7);

        $this->db->query('INSERT INTO {dbprefix}bolido_tests (name, password) VALUES (?, ?)', array('siete', 'ocho'));
        $this->assertEquals($this->db->insertId(), 8);
        $this->db->freeResult();
    }

    /**
     * Test Fetch Methods
     */
    public function testQueryFetch()
    {
        $this->db->query('SELECT password, email
                          FROM {dbprefix}bolido_tests
                          WHERE name = ?', array('1'));

        $this->assertEquals($this->db->fetchAll(), array(array('password' => '2', 'email' => '3')));

        $this->db->query('SELECT password, email
                          FROM {dbprefix}bolido_tests
                          WHERE name = ?', array('4'));

        $this->assertEquals($this->db->fetchRow(), array('password' => '5', 'email' => '6'));

        $this->db->query('SELECT name, password, email
                          FROM {dbprefix}bolido_tests');

        $this->assertEquals($this->db->fetchAll(), array(array('name' => '1', 'password' => '2', 'email' => '3'),
                                                         array('name' => '4', 'password' => '5', 'email' => '6'),
                                                         array('name' => '7', 'password' => '8', 'email' => '9'),
                                                         array('name' => '10', 'password' => '11', 'email' => '12')));
        $this->db->freeResult();
    }

    /**
     * Test Fetch Column
     */
    public function testFetchColumn()
    {
        $this->db->query('SELECT COUNT(password)
                          FROM {dbprefix}bolido_tests
                          WHERE name IN (?, ?)', array('1', '4'));

        $this->assertEquals($this->db->fetchColumn(), 2);
        $this->db->freeResult();
    }

    /**
     * Test Affected Rows
     */
    public function testAffectedRows()
    {
         $this->db->query('SELECT COUNT(*)
                          FROM {dbprefix}bolido_tests
                          WHERE name <= ?', array('7'));

        $rows = $this->db->fetchColumn();

        $this->db->query('DELETE FROM {dbprefix}bolido_tests WHERE name <= ?', array(7));
        $this->assertEquals($this->db->affectedRows(), $rows);
        $this->db->freeResult();
    }

    /**
     * Test Last Query Debug
     */
    public function testLastQueryDebug()
    {
        $this->db->query('SELECT COUNT(*) FROM {dbprefix}bolido_tests WHERE name <= ?', array('7'));

        $debug = $this->db->debug();
        $this->assertEquals($debug['last_query'], 'SELECT COUNT(*) FROM bld_bolido_tests WHERE name <= ?');
        unset($debug['last_error'], $debug['last_query'], $debug['inTransaction'], $debug['autocomit']);

        $debugString = $this->db;
        $this->assertEquals($debugString, print_r($debug, true));
        $this->db->freeResult();
    }

    /**
     * Test Transactions
     */
    public function testTransactions()
    {
        $this->db->enableAutocommit(false);

        $this->db->query('INSERT INTO {dbprefix}bolido_tests (name) VALUES (?)', 'mike');
        $this->assertEquals($this->db->insertId(), 5);

        $this->db->query('SELECT user_id FROM {dbprefix}bolido_tests WHERE name = ?', 'mike');
        $id = $this->db->fetchColumn();
        $this->assertEquals(5, $id);

        $this->assertTrue($this->db->rollBack());
        $this->db->query('SELECT user_id FROM {dbprefix}bolido_tests WHERE name = ?', 'mike');
        $id = $this->db->fetchColumn();
        $this->assertEquals(0, $id);

        $this->db->beginTransaction();
        $this->db->query('INSERT INTO {dbprefix}bolido_tests (name) VALUES (?)', 'aloha');
        $this->assertEquals($this->db->insertId(), 6);
        $this->assertTrue($this->db->commit());

        $this->db->query('SELECT user_id FROM {dbprefix}bolido_tests WHERE name = ?', 'aloha');
        $id = $this->db->fetchColumn();
        $this->assertEquals(6, $id);

        $this->assertTrue($this->db->beginTransaction());
        $this->assertFalse($this->db->beginTransaction());
        $this->db->query('INSERT INTO {dbprefix}bolido_tests (name) VALUES (?)', 'no ni');
        $this->assertEquals($this->db->insertId(), 7);
        $this->assertTrue($this->db->commit());
        $this->assertFalse($this->db->commit());
        $this->assertFalse($this->db->rollBack());

        $this->db->query('SELECT user_id FROM {dbprefix}bolido_tests WHERE name = ?', 'no ni');
        $id = $this->db->fetchColumn();
        $this->assertEquals(7, $id);

        $this->db->__destruct();
    }

    /**
     * Test Quote method
     */
    public function testQuote()
    {
        $this->assertEquals("'string'", $this->db->quote('string'));
        $this->assertEquals("'string with space'", $this->db->quote('string with space'));
        $this->assertEquals("'string with %'", $this->db->quote('string with %'));
        $this->assertEquals("'string with \''", $this->db->quote('string with \''));
        $this->assertEquals("'\''", $this->db->quote('\''));
        $this->assertEquals("'string with stuff'", $this->db->quote('string with stuff'));
        $this->assertEquals("'%'", $this->db->quote('%'));
        $this->assertEquals("'_'", $this->db->quote('_'));
        $this->assertEquals("'\\\'", $this->db->quote('\\'));
        $this->assertEquals("''", $this->db->quote(null));
        $this->assertEquals("'-'", $this->db->quote('-'));
        $this->assertEquals("'@'", $this->db->quote('@'));
        $this->assertEquals("'áéí ó ú'", $this->db->quote('áéí ó ú'));
        $this->assertEquals("'ño ño ño'", $this->db->quote('ño ño ño'));
        $this->assertEquals("':)'", $this->db->quote(':)'));
    }

    /**
     * Test Script runned
     */
    public function testScriptRead()
    {
        $this->db->runScript(__DIR__ . '/../Workspace/sql/mysql.sql');
        for ($i=1; $i<=9; $i++)
        {
            $this->db->query('SELECT user_id FROM {dbprefix}bolido_tests WHERE name = ?', 'mike' . $i);
            $this->assertTrue(($this->db->fetchColumn() > 0));
        }

        $this->setExpectedException('Exception');
        $this->db->runScript('this/script/doesnt/exists.sql');
    }
}
?>
