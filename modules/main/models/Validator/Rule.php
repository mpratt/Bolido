<?php
/**
 * Rule.php
 * Abstract class that every validator rule should extend
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

abstract class Rule
{
    /**
     * Actually validates the $value
     *
     * @param mixed $value
     * @return bool
     */
    abstract public function validate($value);

    /**
     * Returns the translated error message
     *
     * @param object $land
     * @param string $field
     * @param mixed  $value
     * @return string
     */
    abstract public function getErrorMessage(\Bolido\Lang $lang, $field, $value);
}
?>
