<?php
/**
 * Url.php
 * A rule that validates only Url strings
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

class Url extends \Bolido\Modules\main\models\Validator\Rule
{
    /**
     * Actually validates the $value
     *
     * @param string $value
     * @return bool
     */
    public function validate($value)
    {
        if (strlen($value) < 3 || !filter_var($value, FILTER_VALIDATE_URL))
            return false;

        $check = @parse_url($value);
        return (is_array($check) && isset($check['scheme']) && isset($check['host']) && count(explode('.', $check['host'])) > 1);
    }

    /**
     * Returns the translated error message
     *
     * @param object $land
     * @param string $field
     * @param mixed  $value
     * @return string
     */
    public function getErrorMessage(\Bolido\Lang $lang, $field, $value) { return $lang->get('main_validator_invalid_url', $field, $value); }
}

?>
