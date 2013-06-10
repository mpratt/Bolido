<?php
/**
 * TestMailer.php
 *
 * @package Tests
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
use \Bolido\Modules\main\models\Mailer as Mailer;

class TestMailer extends PHPUnit_Framework_TestCase
{
    public function testMailerConstruction()
    {
        $from = 'mail@domain.com';
        $to = 'destiny@domain.com';
        $subject = 'This is a subject';
        $body = 'This is the body';

        $mailer = new Mailer($from, $to, $subject, $body, true);
        $this->assertEquals($mailer->from, $from);
        $this->assertEquals($mailer->to, $to);
        $this->assertEquals($mailer->subject, $subject);
        $this->assertEquals($mailer->body, $body);

        $mailer = new Mailer($from, $to, $subject, $body, false);
        $this->assertEquals($mailer->from, $from);
        $this->assertEquals($mailer->to, $to);
        $this->assertEquals($mailer->subject, $subject);
        $this->assertEquals($mailer->body, $body);
    }

    public function testMailerHeaders()
    {
        $from = 'mail@domain.com';
        $to = 'destiny@domain.com';
        $subject = 'This is a subject';
        $body = 'This is the body';
        $mailer = new Mailer($from, $to, $subject, $body, true);

        $headers = array('title' => 'content',
                         'stuff' => 'Other Contet',
                         'From'  => 'other@email.com');

        foreach ($headers as $k => $v)
            $mailer->addMailHeader($k, $v);

        $headers2 = $mailer->headers;
        foreach($headers2 as $k => $v)
        {
            if (isset($headers[$k]))
                $this->assertEquals($v, $headers[$k]);
        }


        $defaultHeaders = array('From', 'Reply-To', 'Return-Path', 'X-mailer', 'MIME-Version', 'Content-type');
        foreach($defaultHeaders as $v)
            $this->assertTrue(isset($headers2[$v]));
    }

    public function testMailerInvalidSender()
    {
        $this->setExpectedException('InvalidArgumentException');

        $from = 'Abc.example.com';
        $to = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidSender2()
    {
        $this->setExpectedException('InvalidArgumentException');

        $from = 'Abc.@example.com';
        $to = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidSender3()
    {
        $this->setExpectedException('InvalidArgumentException');

        $from = 'Abc..123@example.com';
        $to = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidSender4()
    {
        $this->setExpectedException('InvalidArgumentException');

        $from = 'A@b@c@example.com';
        $to = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidSender5()
    {
        $this->setExpectedException('InvalidArgumentException');

        $from = 'a"b(c)d,e:f;g<h>i[j\k]l@example.com';
        $to = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidSender6()
    {
        $this->setExpectedException('InvalidArgumentException');

        $from = 'this is"not\allowed@example.com';
        $to = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidRecipient()
    {
        $this->setExpectedException('InvalidArgumentException');

        $to = 'Abc.example.com';
        $from = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidRecipient2()
    {
        $this->setExpectedException('InvalidArgumentException');

        $to = 'Abc.@example.com';
        $from = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidRecipient3()
    {
        $this->setExpectedException('InvalidArgumentException');

        $to = 'Abc..123@example.com';
        $from = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidRecipient4()
    {
        $this->setExpectedException('InvalidArgumentException');

        $to = 'A@b@c@example.com';
        $from = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidRecipient5()
    {
        $this->setExpectedException('InvalidArgumentException');

        $to = 'a"b(c)d,e:f;g<h>i[j\k]l@example.com';
        $from = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }

    public function testMailerInvalidRecipient6()
    {
        $this->setExpectedException('InvalidArgumentException');

        $to = 'this is"not\allowed@example.com';
        $from = 'destiny@domain.com';
        $mailer = new Mailer($from, $to,'Subject', 'Body');
        $mailer->send();
    }
}
?>
