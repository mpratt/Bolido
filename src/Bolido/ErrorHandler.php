<?php
/**
 * ErrorHandler.php
 * Handles errors and exceptions.
 *
 * @package Bolido
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
    protected $app;
    protected $registry = array();
    protected $errorCount = 0;

    /**
     * Construct
     *
     * @param object $app
     * @param object $twig
     * @return void
     */
    public function __construct(\Bolido\Container $app) { $this->app = $app; }

    /**
     * Register the error handling methods
     *
     * @return void
     *
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
     *
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
     * Shows a nice error webpage
     *
     * @param object $exception
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function exceptionHandler($exception)
    {
        $this->saveMessage($exception->getMessage(), $exception->getTraceAsString());
        $this->display($exception->getMessage(), 500);
    }

    /**
     * Handle all those pesky fatal errors that
     * are not shown/catched at the end of a request.
     *
     * This method is also used to write errors into a log.
     *
     * @return void
     *
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
     * registry array property.
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
                                           'message' => $this->app['lang']->get($message),
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
        $this->registry = $this->app['hooks']->run('error_log', $this->registry);
        if (empty($this->registry) || !is_writeable(LOGS_DIR))
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
     *
     * @return int
     */
    public function totalErrors() { return count($this->registry); }

    /**
     * Displays a fatal error
     *
     * @param string $message
     * @param int $code
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function display($message, $code = 500)
    {
        $this->writeLog();
        if (!is_numeric($code) || !in_array($code, array(401, 404, 500, 503)))
            $code = 500;

        $httpHeaders = array('401' => 'HTTP/1.1 401 Unauthorized',
                             '404' => 'HTTP/1.1 404 Not Found',
                             '500' => 'HTTP/1.1 500 Internal Server Error',
                             '503' => 'HTTP/1.1 503 Service Unavailable');

        $this->app['hooks']->run('before_error_display', $message, $code, $httpHeaders[$code], $this->app);
        $values = array('message' => $message, 'code' => $code);

        header($httpHeaders[$code]);
        die($this->app['twig']->render('main/http-error', $values));
    }
}
?>
