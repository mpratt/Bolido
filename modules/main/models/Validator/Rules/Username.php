<?php
/**
 * Username.php
 * A rule that validates Username strings
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

class Username extends \Bolido\Modules\main\models\Validator\Rule
{
    protected $normalRegex = '~^[\w\pL\.\d-_]+$~iu';
    protected $spacesRegex = '~^[\w\pL\.\d- _]+$~iu';
    protected $regex;

    /**
     * Initializes the object
     *
     * @param bool $allowSpaces
     * @return void
     */
    public function __construct($allowSpaces = false)
    {
        if ($allowSpaces)
            $this->regex = $this->spacesRegex;
        else
            $this->regex = $this->normalRegex;
    }

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
    public function getErrorMessage(\Bolido\Lang $lang, $field, $value) { return $lang->get('main_validator_invalid_username', $field, $value); }
}

?>
