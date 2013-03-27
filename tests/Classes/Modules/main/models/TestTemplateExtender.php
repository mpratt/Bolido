<?php
/**
 * TestTemplateExtender.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
use \Bolido\Modules\main\models\TemplateExtender as TemplateExtender;

require_once(BASE_DIR . '/../modules/main/models/TemplateExtender.php');
class TestTemplateExtender extends PHPUnit_Framework_TestCase
{
    /**
     * Test Append to Header
     */
    public function testAppendToHeader()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->appendToHeader('code 1');
        $extender->appendToHeader('code 2');
        $extender->appendToHeader('code 3');
        $extender->appendToHeader('code 4');
        $extender->appendToHeader('code 5');
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('code 1', 'code 2', 'code 3', 'code 4', 'code 5'));
    }

    /**
     * Test Append to Footer
     */
    public function testAppendToFooter()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->appendToFooter('code 1');
        $extender->appendToFooter('code 2');
        $extender->appendToFooter('code 3');
        $extender->appendToFooter('code 4');
        $extender->appendToFooter('code 5');
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toFooter'], array('code 1', 'code 2', 'code 3', 'code 4', 'code 5'));
    }

    /**
     * Test Set Html title
     */
    public function testSetHtmltitle()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->setHtmlTitle('This is My title', false);
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<title>This is My title</title>'));
    }

    /**
     * Test Set Html title
     */
    public function testSetHtmltitle2()
    {
        $config = new Testconfig();
        $config->siteTitle = 'Domain.com';

        $template = new MockTemplate();
        $extender = new TemplateExtender($config, new MockLang());
        $extender->setHtmlTitle('This is My title');
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<title>Domain.com - This is My title</title>'));
    }

    /**
     * Test Set Html title
     */
    public function testSetHtmltitle3()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->setHtmlTitle('This is My First title', false);
        $this->assertTrue($extender->hasTitle());

        $extender->setHtmlTitle('This is My Second title', false);
        $this->assertTrue($extender->hasTitle());

        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<title>This is My First title</title>'));
    }

    /**
     * Test Set Html Description
     */
    public function testSetHtmlDescription()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->setHtmlDescription('This is My description');
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<meta name="description" content="This is My description">'));
    }

    /**
     * Test Set Html Description
     */
    public function testSetHtmlDescription2()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $description = implode(' ', range(0, 1000));

        $extender->setHtmlDescription($description);
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<meta name="description" content="' . substr($description, 0, 501) . '...' . '">'));
    }

    /**
     * Test Set Html Description
     */
    public function testSetHtmlDescription3()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $description = implode(' ', range(0, 1000));

        $this->assertFalse($extender->hasDescription());

        $extender->setHtmlDescription($description);
        $this->assertTrue($extender->hasDescription());

        $extender->setHtmlDescription('This is a description');
        $this->assertTrue($extender->hasDescription());

        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<meta name="description" content="' . substr($description, 0, 501) . '...' . '">'));
    }

    /**
     * Test allow Html Indexing
     */
    public function testAllowHtmlIndexing()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->allowHtmlIndexing(true);
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array());
    }

    /**
     * Test allow Html Indexing
     */
    public function testAllowHtmlIndexing2()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->allowHtmlIndexing(false);
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<meta name="robots" content="noindex, nofollow, noimageindex, noarchive">'));
    }

    /**
     * Test css
     */
    public function testCss()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->css('http://www.s.com/media/main1.css');
        $extender->css('http://www.s.com/media/main2.css?query=hi');
        $extender->appendToTemplate($template);
        $expected = array('<link rel="stylesheet" href="http://www.s.com/media/main1.css" type="text/css">',
                          '<link rel="stylesheet" href="http://www.s.com/media/main2.css?query=hi" type="text/css">');

        $this->assertEquals($template->values['toHeader'], $expected);
    }

    /**
     * Test css
     */
    public function testJs()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->js('http://www.s.com/media/main1.js');
        $extender->js('http://www.s.com/media/main2.js?query=hi');
        $extender->appendToTemplate($template);
        $expected = array('<script type="text/javascript" src="http://www.s.com/media/main1.js"></script>',
                          '<script type="text/javascript" src="http://www.s.com/media/main2.js?query=hi"></script>');

        $this->assertEquals($template->values['toHeader'], $expected);
    }

    /**
     * Test js
     */
    public function testFjs()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->fjs('http://www.s.com/media/main1.js');
        $extender->fjs('http://www.s.com/media/main2.js?query=hi');
        $extender->appendToTemplate($template);
        $expected = array('<script type="text/javascript" src="http://www.s.com/media/main1.js"></script>',
                          '<script type="text/javascript" src="http://www.s.com/media/main2.js?query=hi"></script>');

        $this->assertEquals($template->values['toFooter'], $expected);
    }

    /**
     * Test js
     */
    public function testIjs()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());

        $js = 'var hi = 0;
               var ho = 10;
               alert(ho + hi);';

        $extender->ijs($js);
        $extender->appendToTemplate($template);
        $this->assertEquals($template->values['toHeader'], array('<script type="text/javascript">' . $js . '</script>'));
    }

    /**
     * Test js
     */
    public function testFijs()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());

        $js = 'var hi = 0;
               var ho = 10;
               alert(ho + hi);';

        $extender->fijs($js);
        $extender->appendToTemplate($template);
        $this->assertEquals($template->values['toFooter'], array('<script type="text/javascript">' . $js . '</script>'));
    }

    /**
     * Test Identical strings
     */
    public function testIdenticalInclusion()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->allowHtmlIndexing(false);
        $extender->js('../js/main.js');
        $extender->allowHtmlIndexing(false);
        $extender->js('../js/main2.js');
        $extender->allowHtmlIndexing(false);
        $extender->js('../js/main.js');
        $extender->js('../js/main.js');
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('<meta name="robots" content="noindex, nofollow, noimageindex, noarchive">',
                                                                 '<script type="text/javascript" src="../js/main.js"></script>',
                                                                 '<script type="text/javascript" src="../js/main2.js"></script>'));
    }

    /**
     * Test Append Priority
     */
    public function testAppendPriority()
    {
        $template = new MockTemplate();
        $extender = new TemplateExtender(new TestConfig(), new MockLang());
        $extender->appendToHeader('1', 0);
        $extender->appendToHeader('2', 10);
        $extender->appendToHeader('3', 5);
        $extender->appendToHeader('-1', -1);
        $extender->appendToHeader('4');
        $extender->appendToHeader('3', 5);
        $extender->appendToHeader('5', 5);
        $extender->appendToTemplate($template);

        $this->assertEquals($template->values['toHeader'], array('-1', '1', '2', '3', '4', '5'));
    }
}
?>
