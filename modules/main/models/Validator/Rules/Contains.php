<?php
/**
 * Contains.php
 * A rule that validates that the string contains somethind
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

class Contains extends \Bolido\Modules\main\models\Validator\Rule
{
    protected $haystack;

    /**
     * Sets up the value to be matched
     *
     * @param mixed $value
     * @return void
     */
    public function __construct($haystack = null) { $this->haystack = $haystack; }

    /**
     * Actually validates the $value
     *
     * @param string $value
     * @return bool
     */
    public function validate($value)
    {
        if (is_array($this->haystack))
            return in_array($value, $this->haystack);
        else
            return (strpos($value, $this->haystack) !== false);
    }

    /**
     * Returns the translated error message
     *
     * @param object $land
     * @param string $field
     * @param mixed  $value
     * @return string
     */
    public function getErrorMessage(\Bolido\Lang $lang, $field, $value) { return $lang->get('main_validator_invalid_contains', $field, $value); }
}

?>
