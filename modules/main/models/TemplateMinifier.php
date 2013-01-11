<?php
/**
 * TemplateMinifier.php
 * This class Minifies HTML code. Parts of Code were adapted from
 * http://wordpress.org/extend/plugins/w3-total-cache/ by Stephen Clay <steve@mrclay.org>
 *
 * @packag   This file is part of the Bolido Framework
 * @author   Michael Pratt <pratt@hablarmierda.net>
 * @link     http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class TemplateMinifier
{
    protected $placeHolders = array();

    /**
     * Compresses/minifies the html output.
     *
     * @param string $buffer
     * @return string The minified buffer
     */
    public function html($buffer)
    {
        // Do we need to save PREs or TEXTAREAs somewhere in placeholders?
        if (strpos($buffer, '<pre') !== false || strpos($buffer, '<textarea') !== false)
        {
            $buffer = preg_replace_callback('/\\s*(<textarea\\b[^>]*?>[\\s\\S]*?<\\/textarea>)\\s*/i', array($this, 'ignore'), $buffer);
            $buffer = preg_replace_callback('/\\s*(<pre\\b[^>]*?>[\\s\\S]*?<\\/pre>)\\s*/i', array($this, 'ignore'), $buffer);
        }

        // trim each line
        $buffer = preg_replace('/^\\s+|\\s+$/m', '', $buffer);

        // remove ws around block/undisplayed elements
        $buffer = preg_replace('/\\s+(<\\/?(?:area|base(?:font)?|blockquote|body'
            .'|caption|center|cite|col(?:group)?|dd|dir|div|dl|dt|fieldset|form'
            .'|frame(?:set)?|h[1-6]|head|hr|html|legend|li|link|map|menu|meta'
            .'|ol|opt(?:group|ion)|p|param|t(?:able|body|head|d|h||r|foot|itle)'
            .'|ul)\\b[^>]*>)/i', '$1', $buffer);

        // remove ws outside of all elements
        $buffer = preg_replace_callback('/>([^<]+)</', function ($m) {
            return '>' . preg_replace('/^\\s+|\\s+$/', ' ', $m[1]) . '<';
        }, $buffer);

        // replace new lines with spaces
        $buffer = preg_replace('~[\r\n]+~', ' ', $buffer);

        // restore reserved/ignored code when available
        if (!empty($this->placeHolders))
            $buffer = str_replace(array_keys($this->placeHolders), array_values($this->placeHolders), $buffer);

        return $buffer;
    }

    /**
     * This method is used to replace a matched tag
     * with a placeholder. The main use of this method
     * is to leave a portion of text with its original
     * white space.
     *
     * @param array $content
     * @return string
     */
    protected function ignore($content)
    {
        $placeholder = '%' . md5(time() . count($this->placeHolders) . mt_rand(0, 500)) . '%';
        $this->placeHolders[$placeholder] = $content['1'];
        return $placeholder;
    }
}
?>
