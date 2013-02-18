<?php
/**
 * Email.php
 * A rule that validates only email strings
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models\Validator\Rules;

class Email extends \Bolido\Modules\main\models\Validator\Rule
{
    /**
     * Actually validates the $value
     *
     * @param string $value
     * @return bool
     */
    public function validate($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Returns the translated error message
     *
     * @param object $land
     * @param string $field
     * @param mixed  $value
     * @return string
     */
    public function getErrorMessage(\Bolido\Lang $lang, $field, $value) { return $lang->get('main_validator_invalid_email', $field, $value); }
}

?>
