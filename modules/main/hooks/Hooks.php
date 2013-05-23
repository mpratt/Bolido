<?php
/**
 * Hooks.php
 * Registers important functions.
 * This file should remain as is and could be used as a sample.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

/**
 * Appends main I18n files to the language object
 *
 * @param object $lang
 * @return object $lang
 */
$this->append(function ($lang) {
    $lang->load('main/common');
    return $lang;
}, 'modify_lang', 'main');

/**
 * Overwrites some headers
 *
 * @param array $headers
 * @return array
 */
$this->append(function ($headers) {
    $headers['x-powered-by'] = 'Carl Sagan\'s Internet from scratch';
    $headers['server'] = 'Hidden/Unknown';
    return $headers;
}, 'modify_http_headers', 'main');

?>
