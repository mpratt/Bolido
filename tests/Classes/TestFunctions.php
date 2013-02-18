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
     * Test RedirectTo
     */
    public function testRedirectTo()
    {
        $this->setExpectedException('Exception');
        redirectTo('/');
    }

    /**
     * Test Ip and Hostname detection
     */
    public function testIpHostnameDetection()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['REMOTE_HOST'] = 'hellow';
        $this->assertEquals(detectIp(), '192.168.0.1');
        $this->assertEquals(detectHostname(), 'hellow');

        $_SERVER['REMOTE_ADDR'] = 'FE80:0000:0000:0000:0202:B3FF:FE1E:832';
        $_SERVER['REMOTE_HOST'] = '127.0.0.1';
        $this->assertEquals(detectIp(), 'FE80:0000:0000:0000:0202:B3FF:FE1E:832');
        $this->assertEquals(detectHostname(), '127.0.0.1');

        $_SERVER['REMOTE_ADDR'] = '';
        $_SERVER['REMOTE_HOST'] = '';
        $this->assertEquals(detectIp(), null);
        $this->assertEquals(detectHostname(), null);

        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $_SERVER['REMOTE_HOST'] = '';
        $this->assertEquals(detectIp(), '192.168.0.1');
        $this->assertEquals(detectHostname(), '192.168.0.1');
    }
}

?>
