<?php
/**
 * Hooks.php
 * Registers important functions used in many hook events.
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
$hooks['modify_lang'][] = array('from_module' => 'main',
                                'position' => 0,
                                'call' => function ($lang) { $lang->load('main/common'); });
/**
 * Overwrites some headers if possible
 *
 * @param array $headers
 * @return array
 */
$hooks['modify_http_headers'][] = array('from_module' => 'main',
                                        'position' => 0,
                                        'call' => function ($headers) {
                                            $headers['x-powered-by'] = 'Carl Sagan\'s Internet from scratch';
                                            $headers['server'] = 'Hidden/Unknown';
                                            return $headers;
});

/**
 * Minfy the resulting Html page.
 * Priority is set to 99.
 *
 * @param string body
 * @return string
 */
$hooks['filter_template_body'][] = array('from_module' => 'main',
                                         'position' => 99,
                                         'call' => function ($body) {
                                            if (!empty($body))
                                            {
                                                $minify = new \Bolido\Module\main\models\Minify();
                                                $body = $minify->html($body);
                                            }

                                            return $body;
});

/**
 * Extend the template object with a few more
 * methods.
 */
$hooks['extend_template'][] = array('from_module' => 'main',
                                    'position' => 99,
                                    'call' => function ($template) {
                                        $htmlExtender = new \Bolido\Module\main\models\MainTemplateExtender($template->config);
                                        $methods = array('appendToHeader', 'appendToFooter', 'setHtmlTitle', 'setHtmlDescription',
                                                         'allowHtmlIndexing', 'css', 'js', 'fjs', 'ijs', 'fijs', );

                                        foreach ($methods as $m)
                                            $template->extend($m, $htmlExtender);

                                        $template->js('/Modules/main/templates/default/js/jquery-1.7.1.min.js', -100);
                                        $template->js('/Modules/main/templates/default/js/Bolido.js');

                                        $notifyExtender = new \Bolido\Module\main\models\MainNotificationExtender($template->session, $htmlExtender);

                                        $methods = array('Error', 'Warning', 'Success', 'Question');
                                        foreach($methods as $m)
                                            $template->extend('notify' . $m, $notifyExtender);

                                        $template->hooks->append(array('from_module' => 'main',
                                            'call' => function ($template) use (&$htmlExtender, &$notifyExtender){
                                                $notifyExtender->detect();
                                                $htmlExtender->appendToTemplate($template);
                                        }), 'before_template_body');
});

/**
 * Try to enable sessions on the database
 */
$hooks['before_module_execution'][] = array('from_module' => 'main',
                                            'position' => 0,
                                            'call' => function ($app) {
                                        $session = new \Bolido\Module\main\models\MySQLSessionHandler($app['db'], $app['session']);
                                        $session->register();
});

/**
 * Try to enable Error logging on the database
 */
$hooks['before_module_execution'][] = array('from_module' => 'main',
                                            'position' => 0,
                                            'call' => function ($app) {
                                        if (function_exists('detectIp'))
                                        {
                                            try {
                                                $app['db']->query('SELECT * FROM {dbprefix}error_log');
                                                $db = &$app['db'];

                                                $app['hooks']->append(array('from_module' => 'main',
                                                    'call' => function ($errors) use(&$db){
                                                        if (empty($errors) || !is_array($errors))
                                                            return array();

                                                        foreach ($errors as $e)
                                                        {
                                                            $ipBinary = inet_pton($e['ip']);
                                                            $db->query('INSERT INTO {dbprefix}error_log (message, backtrace, ip, date)
                                                                        VALUES (?, ?, ?, ?)', array($e['message'],
                                                                                                    $e['url'] . '    ' . $e['backtrace'],
                                                                                                    $ipBinary,
                                                                                                    date('Y-m-d H:i')));
                                                        }

                                                        return array();
                                                }), 'error_log');

                                            } catch (\Exception $e) {}
                                        }
});
?>
