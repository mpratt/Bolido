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

namespace Bolido\App;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class ErrorHandler
{
    // Object containers
    protected $hooks;
    protected $template;

    protected $registry = array();
    protected $errorCount = 0;
    protected $httpHeaders = array('401' => 'HTTP/1.1 401 Unauthorized',
                                   '404' => 'HTTP/1.1 404 Not Found',
                                   '500' => 'HTTP/1.1 500 Internal Server Error',
                                   '503' => 'HTTP/1.1 503 Service Unavailable');

    /**
     * Construct
     * Sets up the custom error handlers
     *
     * @param object $hooks
     * @param object $template
     * @return void
     */
    public function __construct(\Bolido\App\Hooks $hooks, \Bolido\App\Template &$template)
    {
        $this->hooks = $hooks;
        $this->template = $template;

        set_error_handler(array(&$this, 'errorHandler'));
        set_exception_handler(array(&$this, 'exceptionHandler'));
        register_shutdown_function(array(&$this, 'handleFatalShutdown'));
    }

    /**
     * The custom function that handles error messages triggered by
     * the error_reporting mode
     *
     * @return true (it must be true so that PHP doesnt execute its internal error handler)
     */
    public function errorHandler($level, $message, $file, $line)
    {
        $this->register($message);

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
     */
    public function exceptionHandler($exception)
    {
        $this->register($exception->getMessage(), $exception->getTraceAsString());
        $this->display($exception->getMessage(), 500);
    }

    /**
     * Handle all those pesky fatal errors that
     * are not shown/catched
     *
     * @return void
     */
    public function handleFatalShutdown()
    {
        $error = error_get_last();
        if (!is_null($error) && $error['type'] == 1)
        {
            $this->register($error['message']);
            $this->writeLog();
            $this->display($error['message'] . ' Line ' . $error['line'] . ', File ' . basename($error['file']), 500);
        }

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
    public function register($message, $backtrace = '')
    {
        $hash = md5($message . $backtrace);
        if (!isset($this->registry[$hash]))
        {
            $this->registry[$hash] = array('date' => date('Y-m-d H:i:s'),
                                           'url'  => (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'UNKNOWN'),
                                           'message' => $message,
                                           'backtrace' => (empty($backtrace) ? $this->backtrace : $backtrace));
        }
    }

    /**
     * Actually logs the messages to a file
     *
     * @return void
     */
    protected function writeLog()
    {
        $this->registry = $this->hooks->run('error_log', $this->registry);
        if (empty($this->registry) || !defined('LOGS_DIR') || !is_writeable(LOGS_DIR))
            return ;

        $logFile = LOGS_DIR . '/errors-' . date('Y-m-d') . '.log';
        foreach($this->registry as $log)
        {
            $line = $log['date'] . "\t" . $log['message'] . "\t" . $log['url'] . "\t" . $log['backtrace'] . PHP_EOL;
            file_put_contents($logFile, $line, FILE_APPEND);
        }
    }

    /**
     * Returns the backtrace of the error
     * @return string
     */
    protected function backtrace()
    {
        $backtrace = '';
        if (function_exists('debug_backtrace'))
        {
            foreach (debug_backtrace() as $step)
            {
                $backtrace .= (isset($step['file']) ? basename($step['file']) . ' ' : '') . (isset($step['function']) ? '(' . $step['function'] . ') ' : '') . (isset($step['line']) ? ':' . $step['line'] . '' : '') . PHP_EOL;
            }
            unset($step);
        }

        return $backtrace;
    }

    /**
     * Returns all the errors registered
     * @return int
     */
    public function totalErrors() { return count($this->registry); }

    /**
     * Displays a fatal error
     * @return void
     */
    public function display($message = '', $code = 500,  $errorTemplate = 'main/http-error')
    {
        $mainHeader = (!isset($this->httpHeaders[$code]) ? $this->httpHeaders[500] : $this->httpHeaders[$code]);
        $message  = ($lang->exists($message) ? $lang->get($message) : $message);

        $this->template->load($errorTemplate, array('message' => $message, 'code' => $code));
        $this->template->setHtmlTitle('Fatal Error - Oops! Error!');

        if (!headers_sent())
        {
            header($mainHeader);
            header('Expires: Mon, 20 Jan 1982 04:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        }

        $this->template->display();
        die();
    }
}
?>
