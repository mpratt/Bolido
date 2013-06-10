<?php
/**
 * Equal.php
 * A rule that validates equal strings
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

class Equal extends \Bolido\Modules\main\models\Validator\Rule
{
    protected $value;

    /**
     * Sets up the value to be matched
     *
     * @param mixed $value
     * @return void
     */
    public function __construct($value = null) { $this->value = $value; }

    /**
     * Actually validates the $value
     *
     * @param string $value
     * @return bool
     */
    public function validate($value) { return $this->value == $value; }

    /**
     * Returns the translated error message
     *
     * @param object $land
     * @param string $field
     * @param mixed  $value
     * @return string
     */
    public function getErrorMessage(\Bolido\Lang $lang, $field, $value) { return $lang->get('main_validator_invalid_equal', $field, $value, $this->value); }
}

?>
