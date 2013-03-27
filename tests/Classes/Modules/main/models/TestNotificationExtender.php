<?php
/**
 * TestNotificationExtender.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
use \Bolido\Modules\main\models\NotificationExtender as Notification;
use \Bolido\Modules\main\models\TemplateExtender as TemplateExtender;

require_once(BASE_DIR . '/../modules/main/models/TemplateExtender.php');
require_once(BASE_DIR . '/../modules/main/models/NotificationExtender.php');
class TestNotificationExtender extends PHPUnit_Framework_TestCase
{
    protected $extender, $config;

    /**
     * Setup the environment
     */
    public function setUp()
    {
        $this->config = new TestConfig();
        $this->config->mainUrl = 'http://example.com';
        $this->extender = new TemplateExtender(new TestConfig(), new MockLang());
    }

    /**
     * Tests the detection of new notifications
     */
    public function testNotificationDetection()
    {
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $session  = new MockSession();
        $noti = new Notification($session, new MockLang(), $extender);
        $noti->detect($this->config);

        $this->assertFalse($session->has('bolidoHtmlNotifications'));
    }

    /**
     * Tests the detection of new notifications
     */
    public function testNotificationDetection2()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $session  = new MockSession();
        $session->set('bolidoHtmlNotifications', array('message' => 'hello'));

        $noti = new Notification($session, new MockLang(), $this->extender);
        $this->assertTrue($session->has('bolidoHtmlNotifications'));
        $noti->detect($this->config);
        $this->assertFalse($session->has('bolidoHtmlNotifications'));

        $this->extender->appendToTemplate($template);
        $this->assertEquals($template->values['toFooter'], array('<script type="text/javascript">$(function(){ Bolido.notify(' . json_encode(array('message' => 'hello')) . '); })</script>'));
    }

    /**
     * Tests the detection of new notifications
     */
    public function testNotificationDetection3()
    {
        $values = array();
        $values[] = array('m' => 'hello World');
        $values[] = array('m' => 'hello Friends');
        $values[] = array('m' => 'hello Universe');

        $template = new MockTemplate();
        $session  = new MockSession();
        $session->set('bolidoHtmlNotifications', $values);

        $noti = new Notification($session, new MockLang(), $this->extender);
        $this->assertTrue($session->has('bolidoHtmlNotifications'));
        $noti->detect($this->config);
        $this->assertFalse($session->has('bolidoHtmlNotifications'));

        $this->extender->appendToTemplate($template);
        $this->assertEquals($template->values['toFooter'], array('<script type="text/javascript">$(function(){ Bolido.notify(' . json_encode($values) . '); })</script>'));
    }
    /**
     * Tests the detection of new notifications
     */
    public function testNotificationDetection4()
    {
        $template = new MockTemplate();
        $session  = new MockSession();
        $session->set('bolidoHtmlNotifications', 'this is a string');

        $noti = new Notification($session, new MockLang(), $this->extender);
        $this->assertTrue($session->has('bolidoHtmlNotifications'));
        $noti->detect($this->config);
        $this->assertFalse($session->has('bolidoHtmlNotifications'));

        $this->extender->appendToTemplate($template);
        $this->assertEquals($template->values['toFooter'], array('<script type="text/javascript">$(function(){ Bolido.notify(' . json_encode((array) 'this is a string') . '); })</script>'));
    }

    /**
     * Test error notification
     */
    public function testErrorNotification()
    {
        $expected = array();
        $session  = new MockSession();
        $noti = new Notification($session, new MockLang(), $this->extender);
        $noti->notifyError('Error Message 1', 'body');
        $expected[] = array('message' => 'Error Message 1',
                            'class' => 'bolido-error',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);

        $noti->notifyError('Error Message 2', 'other-place', 9);
        $expected[] = array('message' => 'Error Message 2',
                            'class' => 'bolido-error',
                            'prepend' => 'other-place',
                            'delay' => 9);

        $noti->notifyError('Error Message 3', 'body', 'a string');
        $expected[] = array('message' => 'Error Message 3',
                            'class' => 'bolido-error',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);
    }

    /**
     * Test warning notification
     */
    public function testWarningNotification()
    {
        $expected = array();
        $session  = new MockSession();
        $noti = new Notification($session, new MockLang(), $this->extender);
        $noti->notifyWarning('Warning Message 1', 'body');
        $expected[] = array('message' => 'Warning Message 1',
                            'class' => 'bolido-warning',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);

        $noti->notifyWarning('Warning Message 2', 'other-place', 9);
        $expected[] = array('message' => 'Warning Message 2',
                            'class' => 'bolido-warning',
                            'prepend' => 'other-place',
                            'delay' => 9);

        $noti->notifyWarning('Warning Message 3', 'body', 'a string');
        $expected[] = array('message' => 'Warning Message 3',
                            'class' => 'bolido-warning',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);
    }

    /**
     * Test success notification
     */
    public function testSuccessNotification()
    {
        $expected = array();
        $session  = new MockSession();
        $noti = new Notification($session, new MockLang(), $this->extender);
        $noti->notifySuccess('Success Message 1', 'body');
        $expected[] = array('message' => 'Success Message 1',
                            'class' => 'bolido-success',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);

        $noti->notifySuccess('Success Message 2', 'other-place', 9);
        $expected[] = array('message' => 'Success Message 2',
                            'class' => 'bolido-success',
                            'prepend' => 'other-place',
                            'delay' => 9);

        $noti->notifySuccess('Success Message 3', 'body', 'a string');
        $expected[] = array('message' => 'Success Message 3',
                            'class' => 'bolido-success',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);
    }

    /**
     * Test question notification
     */
    public function testQuestionNotification()
    {
        $expected = array();
        $session  = new MockSession();
        $noti = new Notification($session, new MockLang(), $this->extender);
        $noti->notifyQuestion('Question Message 1', 'body');
        $expected[] = array('message' => 'Question Message 1',
                            'class' => 'bolido-question',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);

        $noti->notifyQuestion('Question Message 2', 'other-place', 9);
        $expected[] = array('message' => 'Question Message 2',
                            'class' => 'bolido-question',
                            'prepend' => 'other-place',
                            'delay' => 9);

        $noti->notifyQuestion('Question Message 3', 'body', 'a string');
        $expected[] = array('message' => 'Question Message 3',
                            'class' => 'bolido-question',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);
    }

    /**
     * Test mixed notification
     */
    public function testMixedNotification()
    {
        $expected = array();
        $session  = new MockSession();
        $noti = new Notification($session, new MockLang(), $this->extender);
        $noti->notifyQuestion('Question Message 1', 'body');
        $expected[] = array('message' => 'Question Message 1',
                            'class' => 'bolido-question',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);

        $noti->notifySuccess('Success Message 1', 'other-place', 9);
        $expected[] = array('message' => 'Success Message 1',
                            'class' => 'bolido-success',
                            'prepend' => 'other-place',
                            'delay' => 9);

        $noti->notifyQuestion('Question Message 3', 'body', 'a string');
        $expected[] = array('message' => 'Question Message 3',
                            'class' => 'bolido-question',
                            'prepend' => 'body',
                            'delay' => 0);

        $this->assertEquals($session->get('bolidoHtmlNotifications'), $expected);
    }
}
?>
