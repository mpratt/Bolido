<?php
/**
 * Validator.class.php, validation class
 * This class has many useful string validation methods.
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

class Validator
{
    protected $value;

    /**
     * Construct
     *
     * @param string $value
     * @return void
     */
    public function __construct($value) { $this->value = $value }

    /**
     * Checks if the $value property is an integer. In practice it only checks
     * if the property is numeric, it does not check for the integer range.
     *
     * @param bool $onlyPositive
     * @return bool
     */
    public function isInteger($onlyPositive = false)
    {
        if ($onlyPositive)
            return (is_numeric($this->value) && preg_replace('~^([0-9]+)$~', '', $this->value) == '');

        return (is_numeric($this->value) && preg_replace('~^([-]{1})?([0-9]+)$~', '', $this->value) == '');
    }

    /**
     * Checks if the $value property contains only alphabetic characters
     * @return bool
     */
    public function isAlpha() { return ctype_alpha($this->value); }

    /**
     * Checks if the $value property cointains only digits.
     * @return bool
     */
    public function isDigit() { return ctype_digit((string) $this->value); }

    /**
     * Checks if the $value property is hexadecimal.
     * @return bool
     */
    public function isHex() { return ctype_xdigit($this->value); }

    /**
     * Checks if the $value property is an ip.
     * @return bool
     */
    public function isIp() { return (bool) ip2long($this->value); }

    /**
     * Checks if the $value property matches a MySQL date format (YYYY-MM-DD)
     * @return bool
     */
    public function isSqlDate() { return (bool) preg_match('~^(\d{4})-(\d{2})-(\d{2})$~', $this->value); }

    /**
     * Checks if the $value property matches a MySQL date-time format (YYYY-MM-DD HH:MM:SS)
     * @return bool
     */
    public function isSqlDateTime() { return (bool) preg_match('~^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$~', $this->value); }

    /**
     * Checks if the $value property is a email address
     * @return bool
     */
    public function isEmail() { return filter_var($this->value, FILTER_VALIDATE_EMAIL); }

    /**
     * Checks if the $value property is a url
     * @return bool
     */
    public function isUrl() { return filter_var($this->value, FILTER_VALIDATE_URL); }

    /**
     * Destruct
     *
     * @return void
     */
    public function __destruct() { unset($this->value); }
}
?>