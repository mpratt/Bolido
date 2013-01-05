<?php
/**
 * ErrorHandler.php
 * Handles errors and exceptions.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class ErrorHandler
{
    // Object containers
    protected $hooks;
    protected $template;

    protected $registry = array();
    protected $errorCount = 0;

    /**
     * Construct
     * Sets up the custom error handlers
     *
     * @param object $hooks
     * @param object $template
     * @return void
     */
    public function __construct(\Bolido\Hooks $hooks, \Bolido\Template $template)
    {
        $this->hooks = $hooks;
        $this->template = $template;
    }

    /**
     * Register the error handling functions
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function register()
    {
        set_error_handler(array(&$this, 'errorHandler'));
        set_exception_handler(array(&$this, 'exceptionHandler'));
        register_shutdown_function(array(&$this, 'handleFatalShutdown'));
    }

    /**
     * The custom function that handles error messages triggered by
     * the error_reporting mode
     *
     * @return true (it must be true so that PHP doesnt execute its internal error handler)
     * @codeCoverageIgnore
     */
    public function errorHandler($level, $message, $file, $line)
    {
        $this->saveMessage($message, 'File: ' . basename($file) . ' Line: ' . $line);

        // Dont display errors if they are not meaningful - http://php.net/manual/en/errorfunc.constants.php
        if (!in_array($level, array(E_WARNING, E_USER_WARNING, E_DEPRECATED, E_USER_NOTICE, E_NOTICE, E_USER_ERROR)))
            $this->display($message, 500);

        return true;
    }

    /**
     * Exception handler function
     * Shows a nice error dialog
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function exceptionHandler($exception)
    {
        $this->saveMessage($exception->getMessage(), $exception->getTraceAsString());
        $this->display($exception->getMessage(), 500);
    }

    /**
     * Handle all those pesky fatal errors that
     * are not shown/catched
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function handleFatalShutdown()
    {
        $error = error_get_last();
        if (!is_null($error) && $error['type'] == 1)
            $this->display($error['message'] . ' Line ' . $error['line'] . ', File ' . basename($error['file']), 500);

        $this->writeLog();
    }

    /**
     * Registers the messages and stores them in the
     * registry property.
     *
     * @param string $message
     * @param string $backtrace
     * @return void
     */
    public function saveMessage($message, $backtrace = '')
    {
        $hash = md5($message . $backtrace);
        if (!isset($this->registry[$hash]))
        {
            $this->registry[$hash] = array('date' => date('Y-m-d H:i:s'),
                                           'url'  => (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'UNKNOWN'),
                                           'message' => $message,
                                           'ip'   => (function_exists('detectIp') ? detectIp() : $_SERVER['REMOTE_ADDR']),
                                           'backtrace' => $backtrace);
        }
    }

    /**
     * Actually logs the messages to a file
     *
     * @return void
     */
    public function writeLog()
    {
        $this->registry = $this->hooks->run('error_log', $this->registry);
        if (empty($this->registry) || !defined('LOGS_DIR') || !is_writeable(LOGS_DIR))
            return ;

        $logFile = LOGS_DIR . '/errors-' . date('Y-m-d') . '.log';
        foreach($this->registry as $log)
        {
            $line = $log['date'] . "\t" . $log['ip'] . "\t" . $log['message'] . "\t" . $log['url'] . "\t" . $log['backtrace'] . PHP_EOL;
            file_put_contents($logFile, $line, FILE_APPEND);
        }

        $this->registry = array();
    }

    /**
     * Returns all the errors registered
     * @return int
     */
    public function totalErrors() { return count($this->registry); }

    /**
     * Displays a fatal error
     * @return void
     * @codeCoverageIgnore
     */
    public function display($message, $code = 500, $template = 'main/http-error')
    {
        $this->writeLog();

        // Send the correct error header
        $this->hooks->append(function($headers) use ($code) {
            $httpHeaders = array('401' => 'HTTP/1.1 401 Unauthorized',
                                 '404' => 'HTTP/1.1 404 Not Found',
                                 '500' => 'HTTP/1.1 500 Internal Server Error',
                                 '503' => 'HTTP/1.1 503 Service Unavailable');

            $headers[] = (!isset($httpHeaders[$code]) ? $httpHeaders[500] : $httpHeaders[$code]);
            return $headers;
        }, 'modify_http_headers');


        if ($template instanceof \Bolido\Template)
        {
            $template->set('message', $message, true);
            $template->set('code', $code, true);
            $template->display();
        }
        else
        {
            try
            {
               $this->template->setHtmlTitle('Fatal Error - Oops! Error!');
            } catch (\Exception $e) {}

            $this->template->load($template, array('message' => $message, 'code' => $code));
            $this->template->display();
        }

        die();
    }
}
?>
