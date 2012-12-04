<?php
/**
 * TestFunctions.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require_once('../Source/Bolido/Functions.php');
class TestMainInc extends PHPUnit_Framework_TestCase
{
    /**
     * Test The prepareOutput Function
     */
    public function testPrepareOutput()
    {
        // Test Double Encoding
        $this->assertEquals('&lt; &lt;hellow&gt;', sanitize('&lt; <hellow>'));

        // Test string encoding
        $this->assertEquals('&lt;script language=&quot;Javascript&quot;&gt;var hi = &#039;&#039;;&lt;/script&gt;', sanitize('<script language="Javascript">var hi = \'\';</script>'));

        // Test Array Input
        $array = array('Hola Me llamo Michael' => 'Hola Me llamo Michael',
                       'Mi Mamá me Ama' => 'Mi Mamá me Ama',
                       '0 &gt; -3' => '0 > -3',
                       '&quot;Hola&quot; Amigo' => '"Hola" Amigo',
                       'Mister O&#039;connell' => 'Mister O\'connell');

        $this->assertEquals(array_keys($array), sanitize(array_values($array)));

        // Test Allow HTML
        $this->assertEquals('<script>alert("Hi")</script>', sanitize('<script>alert("Hi")</script>', true));
    }

    /**
     * Test The prepareOutput Function
     */
    public function testurlize()
    {
        $this->assertEquals('url-with-this-stuff', urlize('url with this stuff'));
        $this->assertEquals('espanol-es-un-gran-idioma', urlize('español es un gran idioma'));
        //$this->assertEquals('Do6po-pzhalovat', urlize('Добро пожаловать'));
        $this->assertEquals('adios-mama-bebe-papa', urlize('adiós mamá bebé papá'));
        $this->assertEquals('Mister-Oconnell', urlize('Mister O\'connell'));
        $this->assertEquals('', urlize('     '));
        $this->assertEquals('Ubungen-und-FussBall', urlize('Übungen und FußBall'));
        $this->assertEquals('Este-Amigo-tiene', urlize('Este Amigo tiene ganas de jugar', array('ganas', 'de', 'jugar')));
        $this->assertEquals('hoy-viernes', urlize('hoy es viernes', array('es')));
        $this->assertEquals('Tengo', urlize('Téngo un string muy largo', array(), 5));
        $this->assertEquals('-', urlize(' . &-'));
        $this->assertEquals('Guten Tag Herr Ottinger', urlize('Guten Tag Herr Öttinger', array(), 0, false));
    }

    /**
     * Test The isIp Function
     */
    public function testIsIp()
    {
        $this->assertTrue(isIp('192.168.0.1'));
        $this->assertTrue(isIp('127.0.0.1'));
        $this->assertTrue(isIp('123.145.0.45'));
        $this->assertTrue(isIp('150.200.200.1'));
        $this->assertTrue(isIp('FE80:0000:0000:0000:0202:B3FF:FE1E:8329'));
        $this->assertTrue(isIp('FE80::0202:B3FF:FE1E:8329'));

        $this->assertFalse(isIp('[2001:db8:0:1]:80'));
        $this->assertFalse(isIp('990.300.1.1'));
        $this->assertFalse(isIp('192.168.0.1:80'));
    }

    /**
     * Test The isSqlDate and isSQLDateTime functions
     */
    public function testIsSQLDateAndTime()
    {
        $this->assertTrue(isSQLDate('2012-04-12'));
        $this->assertTrue(isSQLDate('2100-12-24'));
        $this->assertTrue(isSQLDateTime('2012-06-06 03:23:00'));
        $this->assertTrue(isSQLDateTime('1982-09-17 16:30:59'));

        $this->assertFalse(isSQLDate('2012/09/9'));
        $this->assertFalse(isSQLDateTime('2012-09-09 4:26'));
    }

    /**
     * Test The isEmail Function
     */
    public function testIsEmail()
    {
        $this->assertTrue(isEmail('hi.mis.ter@myhost.com'));
        $this->assertTrue(isEmail('hi_mister-Mike@myhost.com'));
        $this->assertTrue(isEmail('IHaveEmail@myhost.com.de'));
        $this->assertTrue(isEmail('House+of+Pain@hosting.com'));

        $this->assertFalse(isEmail('Im An Email@host.de'));
        $this->assertFalse(isEmail('hola@localhost')); // I Believe this is correct for PHP >= 5.3
    }

    /**
     * Test The isUrl Function
     */
    public function testIsUrl()
    {
        $this->assertTrue(isUrl('http://www.hablarmierda.net/coso-toso/moso/index.php?hi=no&test=yes'));
        $this->assertTrue(isUrl('ftp://localhost.loc/My_Folder/__/stuff'));
        $this->assertTrue(isUrl('ssl://www.hi.com/House/Door/index.php'));
        $this->assertTrue(isUrl('ssl://www.hi.com:80/House/Door/index.php'));

        $this->assertFalse(isUrl('http://localhost/index.php')); // No TLD
        $this->assertFalse(isUrl('index.php?jump=no;fly=yes'));
        $this->assertFalse(isUrl('http://www.localhost.com/hi<script lang="javascript">hi</script>/money'));
        $this->assertFalse(isUrl('http//192.168.0.1/index.php'));
    }

}
?>
