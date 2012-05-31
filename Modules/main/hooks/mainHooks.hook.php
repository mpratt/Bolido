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

$hooks['modify_http_headers'][] = array('from_module' => 'main',
                                        'position' => 0,
                                        'requires' => __FILE__,
                                        'call' => 'mainSendCustomHeaders');

$hooks['template_append_to_meta_helper'][] = array('from_module' => 'main',
                                                   'position' => 0,
                                                   'requires' => __FILE__,
                                                   'call' => 'mainAppendToMetaHelper');

$hooks['template_register_helpers'][] = array('from_module' => 'main',
                                              'position' => 0,
                                              'requires' => __FILE__,
                                              'call' => 'mainTemplateHelpers');

$hooks['before_module_execution'][] = array('from_module' => 'main',
                                            'position' => 99,
                                            'requires' => __FILE__,
                                            'call' => 'mainSessionHandlerDB');

$hooks['filter_template_body'][] = array('from_module' => 'main',
                                         'position' => 99,
                                         'requires' => realpath(dirname(__FILE__) . '/../models/Minify.model.php'),
                                         'call' => array('Minify', 'html'));

/**
 * Appends I18n files to the language object
 *
 * @param object $lang
 * @return void
 */
function mainRegisterLangs($lang)
{
    $lang->load('main/common');
}

/**
 * Register Template Helpers
 *
 * @param array $helpers
 * @return array
 */
function mainTemplateHelpers($helpers)
{
    require_once(realpath(dirname(__FILE__) . '/../models/TemplateNotificationHelper.model.php'));
    $helpers[] = new TemplateNotificationHelper();

    require_once(realpath(dirname(__FILE__) . '/../models/TemplateMetaHelper.model.php'));
    $helpers[] = new TemplateMetaHelper();

    require_once(realpath(dirname(__FILE__) . '/../models/TemplateTimeHelper.model.php'));
    $helpers[] = new TemplateTimeHelper();

    return $helpers;
}

/**
 * Enables Sessions on the database
 *
 * @param object $db
 * @param object $session
 * @return void
 */
function mainSessionHandlerDB($db, $session)
{
    static $sessionDB = null;

    require_once(realpath(dirname(__FILE__) . '/../models/SessionHandlerDB.model.php'));

    $sessionDB = new SessionHandlerDB($db, $session);
    $sessionDB->register();
}

/**
 * Append important stuff to the Meta Helper
 *
 * @param object $template
 * @return array
 */
function mainAppendToMetaHelper($template)
{
    if (defined('CANONICAL_URL'))
        $template->appendToHeader('<link rel="canonical" href="' . CANONICAL_URL . '" />');

    $template->js('/Modules/main/templates/default/js/jquery-1.7.1.min.js', '-100');

    // This is a javascript that keeps sessions alive!
    $template->fijs('var sessionPingTime = 600000;
                     var nextSessionPing = new Date().getTime() + sessionPingTime;
                     function keepSessionAlive() {
                        if (nextSessionPing <= new Date().getTime()) {
                            var tmpi = new Image();
                            tmpi.src = mainurl + \'/main/alive/?seed=\' + Math.random(); nextSessionPing = new Date().getTime() + sessionPingTime;
                            try { console.log(\'KeepAlive request Sent!\'); } catch (e) {}
                        }
                        window.setTimeout(\'keepSessionAlive();\', 120000);
                     }
                     window.setTimeout(\'keepSessionAlive();\', 300000);');

    return $template;
}

/**
 * Overwrites some headers if possible
 *
 * @param array $headers
 * @return array
 */
function mainSendCustomHeaders($headers)
{
    $headers['x-powered-by'] = 'Carl Sagan\'s Internet from scratch';
    $headers['server'] = 'Hidden/Unknown';

    return $headers;
}

?>
