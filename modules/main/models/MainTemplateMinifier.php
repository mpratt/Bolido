<?php
/**
 * MainTemplateMinifier.php
 * Minifies HTML source
 * Parts of Code were adapted from http://wordpress.org/extend/plugins/w3-total-cache/ by Stephen Clay <steve@mrclay.org>
 *
 * @packag   This file is part of the Bolido Framework
 * @author   Michael Pratt <pratt@hablarmierda.net>
 * @link     http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Module\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class MainTemplateMinifier
{
    protected $placeHolders = array();

    /**
     * Compresses/minifies the html output.
     *
     * @param string $buffer
     * @return string modified $buffer
     */
    public function html($buffer)
    {
        // Do we need to save PREs or TEXTAREAs somewhere in placeholders?
        if (strpos($buffer, '<pre') !== false || strpos($buffer, '<textarea') !== false)
        {
            $buffer = preg_replace_callback('/\\s*(<textarea\\b[^>]*?>[\\s\\S]*?<\\/textarea>)\\s*/i', array($this, 'reserve_this'), $buffer);
            $buffer = preg_replace_callback('/\\s*(<pre\\b[^>]*?>[\\s\\S]*?<\\/pre>)\\s*/i', array($this, 'reserve_this'), $buffer);
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
        $buffer = preg_replace_callback('/>([^<]+)</', array($this, 'trim_outside_tags'), $buffer);

        // replace new lines with spaces
        $buffer = preg_replace('~[\r\n]+~', ' ', $buffer);

        // restore reserved places when needed
        if (!empty($this->placeHolders))
            $buffer = str_replace(array_keys($this->placeHolders), array_values($this->placeHolders), $buffer);

        return $buffer;
    }

    protected function reserve_place($content)
    {
        $placeholder = '%' . md5(time() . count($this->placeHolders) . mt_rand(0, 500)) . '%';
        $this->placeHolders[$placeholder] = $content;
        return $placeholder;
    }

    protected function trim_outside_tags($m) { return '>' . preg_replace('/^\\s+|\\s+$/', ' ', $m[1]) . '<'; }
    protected function reserve_this($m) { return $this->reserve_place($m[1]); }
}
?>
