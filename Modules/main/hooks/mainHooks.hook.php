<?php
/**
 * mainHooks.hook.php
 * Registers important functions used in many hook events.
 * This file should remain as is and could be used as a sample.
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

$hooks['load_langs'][] = array('from_module' => 'main',
                               'position' => 0,
                               'requires' => __FILE__,
                               'call' => 'mainRegisterLangs');

$hooks['before_template_display'][] = array('from_module' => 'main',
                                            'position' => 0,
                                            'requires' => __FILE__,
                                            'call' => 'mainSendCustomHeaders');

$hooks['template_append_to_header'][] = array('from_module' => 'main',
                                              'position' => 0,
                                              'requires' => __FILE__,
                                              'call' => 'mainAppendHeaders');

$hooks['template_append_to_footer'][] = array('from_module' => 'main',
                                              'position' => 0,
                                              'requires' => __FILE__,
                                              'call' => 'mainKeepSessionAlive');

$hooks['template_register_helpers'][] = array('from_module' => 'main',
                                              'position' => 0,
                                              'requires' => __FILE__,
                                              'call' => 'mainTemplateHelpers');

$hooks['filter_template_body'][] = array('from_module' => 'main',
                                         'position' => 99,
                                         'requires' => realpath(dirname(__FILE__) . '/../models/Minify.model.php'),
                                         'call' => array('Minify', 'html'));

/**
 * Appends I18n files to the language object
 *
 * @param array $langs
 * @return array
 */
function mainRegisterLangs($langs = array())
{
    $langs[] = 'main/common';
    return $langs;
}

/**
 * Register Template Helpers
 *
 * @param array $helpers
 * @return array
 */
function mainTemplateHelpers($helpers)
{
    require_once(realpath(dirname(__FILE__) . '/../models/TemplateMetaHelper.model.php'));
    $helpers[] = new TemplateMetaHelper();

    require_once(realpath(dirname(__FILE__) . '/../models/TemplateNotificationHelper.model.php'));
    $helpers[] = new TemplateNotificationHelper();

    require_once(realpath(dirname(__FILE__) . '/../models/TemplateTimeHelper.model.php'));
    $helpers[] = new TemplateTimeHelper();

    return $helpers;
}

/**
 * Appends the canonical url meta tag to html headers
 * and jquery framework.
 *
 * @param object $template
 * @return array
 */
function mainAppendHeaders($template)
{
    if (defined('CANONICAL_URL'))
        $template->appendToHeader('<link rel="canonical" href="' . CANONICAL_URL . '" />');

    $template->js('/Modules/main/templates/default/js/jquery-1.7.1.min.js', '-100');

    return $template;
}

/**
 * Appends a Javascript at the end of the page, that keeps sessions alive.
 *
 * @param object $template
 * @return array
 */
function mainKeepSessionAlive($template)
{
    // This is a javascript that keeps sessions alive!
    $template->fijs('var sessionPingTime = 600000; var nextSessionPing = new Date().getTime() + sessionPingTime;
                     function keepSessionAlive() { if (nextSessionPing <= new Date().getTime()) { var tmpi = new Image(); tmpi.src = mainurl + \'/main/alive/?seed=\' + Math.random(); nextSessionPing = new Date().getTime() + sessionPingTime; try { console.log(\'KeepAlive request Sent!\'); } catch (e) {} } window.setTimeout(\'keepSessionAlive();\', 120000); }
                     window.setTimeout(\'keepSessionAlive();\', 300000);');

    return $template;
}

/**
 * Overwrites some headers if possible
 *
 * @param string $contentType The current Content Type
 * @return bool
 */
function mainSendCustomHeaders($contentType = '')
{
    if (headers_sent())
        return false;

    header('X-Powered-By: Carl Sagan\'s Internet from scratch');
    header('Server: Hidden/Unknown');
    return true;
}

?>