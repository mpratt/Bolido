<?php
/**
 * Regex.php
 * A rule that validates only regex strings
 *
 * @package Module.Main.Models
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models\Validator\Rules;

class Regex extends \Bolido\Modules\main\models\Validator\Rule
{
    protected $regex;

    /**
     * Initializes the object
     *
     * @param string $regex
     * @return void
     */
    public function __construct($regex) { $this->regex = $regex; }

    /**
     * Actually validates the $value
     *
     * @param string $value
     * @return bool
     */
    public function validate($value)
    {
        return preg_match($this->regex, $value);
    }

    /**
     * Returns the translated error message
     *
     * @param object $land
     * @param string $field
     * @param mixed  $value
     * @return string
     */
    public function getErrorMessage(\Bolido\Lang $lang, $field, $value) { return $lang->get('main_validator_invalid_regex', $field, $value); }
}

?>
