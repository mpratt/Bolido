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
 * @return void
 */
$this->append(function ($lang) { $lang->load('main/common'); }, 'modify_lang', 'main');

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

/**
 * Minfy the resulting Html page.
 *
 * @param string body
 * @return string
 */
$this->append(function ($body) {
    if (!empty($body))
    {
        $minify = new \Bolido\Modules\main\models\TemplateMinifier();
        $body = $minify->html($body);
    }

    return $body;
}, 'filter_template_body', 'main', 9999); // try to run this at the end of the queue.

/**
 * Extend the template object with a few more
 * methods. They work if the template has a $toHeader or $toFooter variables
 * inside the used templates. If you use the main/main-header-above.tpl.php or main/main-footer-bottom.tpl.php
 * templates, this methods should work flawlessly.
 *
 * - appendTo{Header,Footer}(string)       : Appends data to the toHeader/toFooter array.
 * - setHtml{Title,Description}(string)    : Appends html title and description to the toHeader array.
 * - allowHtmlIndexing(bool)               : Appends the robots meta tag policy.
 * - css(string), js(string), ijs(string)  : Appends css/javascript/inline javascript strings to the toHeader array
 * - fjs(string), fijs(string)             : Appends javascript/inline javascript to the toFooter array.
 * - notify{Error,Warning,Success,Question}(string): Appends notification javascripts to the toFooter array.
 *                                                   (Needs the Bolido.js to be included in the HTML).
 *
 * @param object $template
 * @return void
 */
$this->append(function ($template) {
    $htmlExtender = new \Bolido\Modules\main\models\TemplateExtender($template->config);
    $methods = array('appendToHeader', 'appendToFooter', 'setHtmlTitle', 'setHtmlDescription',
                     'allowHtmlIndexing', 'css', 'js', 'fjs', 'ijs', 'fijs', );

    foreach ($methods as $m)
        $template->extend($m, array(&$htmlExtender, $m));

    $notifyExtender = new \Bolido\Modules\main\models\NotificationExtender($template->session, $htmlExtender);

    $methods = array('notifyError', 'notifyWarning', 'notifySuccess', 'notifyQuestion');
    foreach($methods as $m)
        $template->extend($m, array(&$notifyExtender, $m));

    $template->hooks->append(function ($template) use (&$htmlExtender, &$notifyExtender){
            $notifyExtender->detect($template->config);
            $htmlExtender->appendToTemplate($template);
    }, 'before_template_body', 'main', 100000);
}, 'extend_template', 'main', -9999); // Register this stuff really early in the game

/**
 * Try to append alternate hreflang tags
 * if we are using more than 1 language.
 *
 * @param object $template
 * @return void
 */
$this->append(function ($template) {
    $default  = $template->config->language;
    $fallback = $template->config->fallbackLanguage;
    $allowed  = $template->config->allowedLanguages;
    $langs    = array_unique(array_merge($allowed, array($default, $fallback)));

    try
    {
        if (count($langs) > 1)
        {
            foreach($langs as $l)
            {
                $url = $template->config->mainUrl . '/?locale=' . $l;
                $tag = '<link rel="alternate" hreflang="' . $l . '" href="' . $url . '">';
                $template->appendToHeader($tag);
            }
        }
    } catch(\Exception $e) {}
}, 'before_template_body', 'main');

?>
