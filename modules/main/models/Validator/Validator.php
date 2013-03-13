<?php
/**
 * Validator.php
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models\Validator;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Validator
{
    protected $lang;
    protected $errors  = array();
    protected $rules   = array();
    protected $filters = array();
    protected $translations = array();

    /**
     * Construct
     *
     * @param object $lang
     * @return void
     */
    public function __construct(\Bolido\Lang $lang)
    {
        $this->lang = $lang;
        $this->lang->load('main/validator');
    }

    /**
     * Validates the data given based
     * on the specified rules.
     *
     * @param array $values Associative array with the values
     * @return bool
     */
    public function validate(array $values = array())
    {
        if (!empty($values))
        {
            $this->errors = array();
            foreach ($values as $field => $value)
            {
                if (!empty($this->filters[$field]))
                    $value = $this->filters[$field]($value);

                if (!empty($this->rules[$field]))
                {
                    $validator = $this->rules[$field];
                    if (isset($this->translations[$field]))
                        $field = $this->translations[$field];

                    if ($validator instanceof \Closure && !$validator($value))
                        $this->errors[$field] = $this->lang->get('main_validator_invalid_value', $field, $value);
                    else if ($validator instanceof \Bolido\Modules\main\models\Validator\Rule && !$validator->validate($value))
                            $this->errors[$field] = $validator->getErrorMessage($this->lang, $field, $value);
                }
            }

            return (empty($this->errors));
        }

        $this->errors[] = $this->lang->get('main_validator_empty_input');
        return false;
    }

    /**
     * Returns an array with all the error messages
     *
     * @return array
     */
    public function getErrors() { return array_reverse($this->errors); }

    /**
     * Applies a filter to a field
     *
     * @param string $field    The name of the field to be validated
     * @param callable $filter The function that should be applied
     * @return void
     */
    public function applyFilter($field, Callable $filter) { $this->filters[$field] = $filter; }

    /**
     * Appends a rule to a field
     *
     * @param string $field  The name of the field to be validated
     * @param callable $rule The rule that should be followed
     * @param string $translation A string used to display in the error message.
     * @return void
     *
     * @throws InvalidArgumentException when an invalid field/rule is given
     */
    public function addRule($field, $rule = null, $translation = null)
    {
        if (empty($field))
            throw new \InvalidArgumentException('Empty field given');

        if ($rule instanceof \Bolido\Modules\main\models\Validator\Rule || is_callable($rule))
        {
            $this->rules[$field] = $rule;
            if (!empty($translation))
                $this->translations[$field] = $translation;

            return ;
        }

        throw new \InvalidArgumentException('Invalid rule given');
    }
}

?>
