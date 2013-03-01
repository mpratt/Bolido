<?php
/**
 * TestValidator.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

require_once(BASE_DIR . '/../modules/main/models/Validator/Validator.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rule.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Alpha.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/AlphaNumeric.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Contains.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Email.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Equal.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Ip.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Numeric.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Regex.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Url.php');
require_once(BASE_DIR . '/../modules/main/models/Validator/Rules/Username.php');

use \Bolido\Modules\main\models\Validator\Validator,
    \Bolido\Modules\main\models\Validator\Rule,
    \Bolido\Modules\main\models\Validator\Rules\Alpha,
    \Bolido\Modules\main\models\Validator\Rules\AlphaNumeric,
    \Bolido\Modules\main\models\Validator\Rules\Contains,
    \Bolido\Modules\main\models\Validator\Rules\Email,
    \Bolido\Modules\main\models\Validator\Rules\Equal,
    \Bolido\Modules\main\models\Validator\Rules\Ip,
    \Bolido\Modules\main\models\Validator\Rules\Numeric,
    \Bolido\Modules\main\models\Validator\Rules\Regex,
    \Bolido\Modules\main\models\Validator\Rules\Url,
    \Bolido\Modules\main\models\Validator\Rules\Username;

class TestValidator extends PHPUnit_Framework_TestCase
{
    /**
     * Test Rule Exceptions
     */
    public function testRuleExceptions()
    {
        $this->setExpectedException('InvalidArgumentException');

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new MockLang());
    }

    /**
     * Test Rule Exceptions
     */
    public function testRuleExceptions2()
    {
        $this->setExpectedException('InvalidArgumentException');

        $validator = new Validator(new MockLang());
        $validator->addRule('field', '');
    }

    /**
     * Test empty flag
     */
    public function testApplyFilter()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Alpha());
        $validator->applyFilter('field', function($value) { return str_replace(' ', '', $value); });

        $this->assertTrue($validator->validate(array('field' => 'asdasdias udiuha sd ado aisd')));
        $this->assertTrue($validator->validate(array('field' => 'asdasde rte rttyutyu')));
        $this->assertTrue($validator->validate(array('field' => 'AfRsdf RgfDFR GSDFG')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Equal('this is my name'));
        $validator->applyFilter('field', function($value) { return strtolower($value); });
        $this->assertTrue($validator->validate(array('field' => 'This IS MY Name')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Alpha());
        $validator->applyFilter('field', 'trim');

        $this->assertTrue($validator->validate(array('field' => ' asdasdias  ')));
    }

    /**
     * Test get Errors
     */
    public function testGetErrors()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Alpha());
        $validator->addRule('field2', new Equal('batman'));

        $this->assertFalse($validator->validate(array('field' => 'asdasdiasudiuhasdadoaisd',
                                                      'field2' => ' asdasd asdasd')));

        $errors = $validator->getErrors();
        $this->assertArrayHasKey('field2', $errors);

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Alpha());
        $validator->addRule('field2', new Equal('batman'));
        $this->assertFalse($validator->validate(array('field' => 'asdasd asdasd asdasd asdasd',
                                                      'field2' => 'batman')));

        $errors = $validator->getErrors();
        $this->assertArrayHasKey('field', $errors);

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Alpha());
        $validator->addRule('field2', new Equal('batman'));
        $this->assertTrue($validator->validate(array('field' => 'asdasdasd', 'field2' => 'batman')));
    }

    /**
     * Test empty validator
     */
    public function testEmptyValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Alpha());
        $this->assertFalse($validator->validate(array()));
    }

    /**
     * Test Closure validator
     */
    public function testClosureValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', function ($value) { return (strlen($value) == 10); });

        $this->assertTrue($validator->validate(array('field' => '1234567890')));
        $this->assertFalse($validator->validate(array('field' => 'asdasd,sdgregerg.')));
    }

    /**
     * Test alpha validator
     */
    public function testAlphaValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Alpha());

        $this->assertTrue($validator->validate(array('field' => 'asdasdiasudiuhasdadoaisd')));
        $this->assertTrue($validator->validate(array('field' => 'asdasderterttyutyu')));
        $this->assertTrue($validator->validate(array('field' => 'AfRsdfRgfDFRGSDFG')));
        $this->assertTrue($validator->validate(array('field' => 'qwertyuuiop')));
        $this->assertTrue($validator->validate(array('field' => 'afghfghfghoiyuowieruoweruo')));
        $this->assertFalse($validator->validate(array('field' => 'asdasd,sdgregerg.')));
        $this->assertFalse($validator->validate(array('field' => 'asd sdf sdf sdf')));
        $this->assertFalse($validator->validate(array('field' => 'AAOOUUííéÚ')));
        $this->assertFalse($validator->validate(array('field' => 'asdasd67979asdasd')));
    }

    /**
     * Test alpha numeric validator
     */
    public function testAlphaNumericValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new AlphaNumeric());

        $this->assertTrue($validator->validate(array('field' => 'asdas567di567567as8u879diuhasdadoaisd')));
        $this->assertTrue($validator->validate(array('field' => 'asdasderter1412300ttyutyu')));
        $this->assertTrue($validator->validate(array('field' => 'AfRsdfRgfDFRGSDFG00123')));
        $this->assertTrue($validator->validate(array('field' => 'qwer00tyuuiop234')));
        $this->assertTrue($validator->validate(array('field' => 'afghfg234hfg345345hoiyuowieruoweruo')));
        $this->assertTrue($validator->validate(array('field' => 'asdasdsdgregerg')));
        $this->assertTrue($validator->validate(array('field' => '12345678910')));
        $this->assertTrue($validator->validate(array('field' => 'asd')));
        $this->assertFalse($validator->validate(array('field' => '123123 ewrwer 234234')));
    }

    /**
     * Test Contains validator
     */
    public function testContainsValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Contains('this is my name'));
        $this->assertTrue($validator->validate(array('field' => 'hi my friends this is my name yeah!')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Contains(array('hello', 'my', 'name')));
        $this->assertTrue($validator->validate(array('field' => 'name')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Contains(array('hello', 'my', 'name')));
        $this->assertFalse($validator->validate(array('field' => 'Name')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Contains('this is my name'));
        $this->assertFalse($validator->validate(array('field' => 'hi my friends this IS my name yeah!')));
    }

    /**
     * Test Equal validator
     */
    public function testCustomTranslations()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Equal('this is my name'), 'stuff');
        $this->assertFalse($validator->validate(array('field' => 'hi my friends this is my name yeah!')));
        foreach ($validator->getErrors() as $m)
            $this->assertContains('stuff', $m);
    }

    /**
     * Test Equal validator
     */
    public function testEqualValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Equal('this is my name'));
        $this->assertFalse($validator->validate(array('field' => 'hi my friends this is my name yeah!')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Equal('hi friends'));
        $this->assertTrue($validator->validate(array('field' => 'hi friends')));
    }

    /**
     * Test Ip Validator
     */
    public function testIpValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('ip', new Ip());

        $this->assertTrue($validator->validate(array('ip' => '192.168.0.1')));
        $this->assertTrue($validator->validate(array('ip' => '127.0.0.1')));
        $this->assertTrue($validator->validate(array('ip' => '123.145.0.45')));
        $this->assertTrue($validator->validate(array('ip' => '150.200.200.1')));
        $this->assertTrue($validator->validate(array('ip' => 'FE80:0000:0000:0000:0202:B3FF:FE1E:8329')));
        $this->assertTrue($validator->validate(array('ip' => 'FE80::0202:B3FF:FE1E:8329')));
        $this->assertFalse($validator->validate(array('ip' => '[2001:db8:0:1]:80')));
        $this->assertFalse($validator->validate(array('ip' => '990.300.1.1')));
        $this->assertFalse($validator->validate(array('ip' => '192.168.0.1:80')));
    }

    /**
     * Test Numeric Validator
     */
    public function testNumericValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('num', new Numeric());

        $this->assertTrue($validator->validate(array('num' => '1212312030192380193')));
        $this->assertTrue($validator->validate(array('num' => '3.1416')));
        $this->assertFalse($validator->validate(array('num' => '123123 123132123 12313213')));
        $this->assertFalse($validator->validate(array('num' => 'sdasd')));
        $this->assertFalse($validator->validate(array('num' => '123.123.123.123')));
    }

    /**
     * Test Regex validator
     */
    public function testRegexValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Regex('~^hi$~'));
        $this->assertFalse($validator->validate(array('field' => 'hi my friends')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Regex('~^yooo~'));
        $this->assertTrue($validator->validate(array('field' => 'yooo my boy!')));
    }

    /**
     * Test Email Validator
     */
    public function testEmailValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('email', new Email());

        $this->assertTrue($validator->validate(array('email' => 'hi.mis.ter@myhost.com')));
        $this->assertTrue($validator->validate(array('email' => 'hi_mister-Mike@myhost.com')));
        $this->assertTrue($validator->validate(array('email' => 'IHaveEmail@myhost.com.de')));
        $this->assertTrue($validator->validate(array('email' => 'House+of+Pain@hosting.com')));
        $this->assertFalse($validator->validate(array('email' => 'Im An Email@host.de')));
        $this->assertFalse($validator->validate(array('email' => 'hola@localhost')));
    }

    /**
     * Test Url validatorn
     */
    public function testUrlValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('url', new Url());

        $this->assertTrue($validator->validate(array('url' => 'http://www.hablarmierda.net/coso-toso/moso/index.php?hi=no&test=yes')));
        $this->assertTrue($validator->validate(array('url' => 'ftp://localhost.loc/My_Folder/__/stuff')));
        $this->assertTrue($validator->validate(array('url' => 'ssl://www.hi.com/House/Door/index.php')));
        $this->assertTrue($validator->validate(array('url' => 'ssl://www.hi.com:80/House/Door/index.php')));
        $this->assertFalse($validator->validate(array('url' => 'http://localhost/index.php')));
        $this->assertFalse($validator->validate(array('url' => 'index.php?jump=no;fly=yes')));
        $this->assertFalse($validator->validate(array('url' => 'http://www.localhost.com/hi<script lang="javascript">hi</script>/money')));
        $this->assertFalse($validator->validate(array('url' => 'http//192.168.0.1/index.php')));
    }

    /**
     * Test Username validator
     */
    public function testUsernameValidator()
    {
        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Username());
        $this->assertTrue($validator->validate(array('field' => 'MyUserName')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Username(true));
        $this->assertTrue($validator->validate(array('field' => 'My UserName With Spaces')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Username());
        $this->assertFalse($validator->validate(array('field' => 'My UserName With Spaces')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Username());
        $this->assertTrue($validator->validate(array('field' => 'MyuserWíthtíldé')));

        $validator = new Validator(new MockLang());
        $validator->addRule('field', new Username());
        $this->assertFalse($validator->validate(array('field' => 'My@UserName!')));
    }
}

?>
