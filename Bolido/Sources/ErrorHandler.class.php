<?php
/**
 * ErrorHandler.class.php
 * Handles errors and exceptions.
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

class ErrorHandler
{
    // Object containers
    protected $config;
    protected $hooks;
    protected $session;
    protected $user;
    protected $db;

    protected $logToDB  = false;
    protected $registry = array();
    protected $httpHeaders = array('401' => 'HTTP/1.0 401 Unauthorized',
                                   '404' => 'HTTP/1.0 404 Not Found',
                                   '500' => 'HTTP/1.0 500 Internal Server Error',
                                   '503' => 'HTTP/1.0 503 Service Unavailable');

    /**
     * Construct
     * Sets up the custom error handlers
     *
     * @param object $config
     * @param object $hooks
     * @return void
     */
    public function __construct(iConfig $config, SessionHandler $session, Hooks $hooks)
    {
        $this->config  = $config;
        $this->hooks   = $hooks;
        $this->session = $session;
        $this->user    = new DummyUser();

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
        $this->log($message, $this->backtrace());

        // Dont display errors if they are not meaningful - http://php.net/manual/en/errorfunc.constants.php
        if (in_array($level, array(E_WARNING, E_USER_WARNING, E_DEPRECATED, E_USER_NOTICE, E_NOTICE, E_USER_ERROR)))
            return true;

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
        $this->log($exception->getMessage(), $exception->getTraceAsString());
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
            $this->log($error['message']);
            $this->display($error['message'] . ' Line ' . $error['line'] . ', File ' . basename($error['file']), 500);
        }
    }

    /**
     * Runs the error_log hook
     * @param string $message
     * @param string $backtrace
     * @return void
     */
    public function log($message, $backtrace = '')
    {
        if (empty($backtrace))
            $backtrace = $this->backtrace();

        $hash = md5($message . $backtrace);
        if (!isset($this->registry[$hash]))
        {
            $backtrace .= ' URL: ' . (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'UNKNOWN');
            $this->registry[$hash] = 1;

            if ($this->logToDB && is_object($this->db) && method_exists($this->db, 'query'))
            {
                $ipBinary = inet_pton(detectIp());
                $this->db->query('INSERT INTO {dbprefix}error_log (message, backtrace, ip, date) VALUES (?, ?, ?, ?)',
                                  array($message, $backtrace, $ipBinary, date('Y-m-d H:i')));
            }

            $this->hooks->run('error_log', $message, $backtrace);
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
                $backtrace .= (isset($step['file']) ? basename($step['file']) . ' ' : '') . (isset($step['function']) ? '(' . $step['function'] . ') ' : '') . (isset($step['line']) ? ':' . $step['line'] . ' ' : '');
            }
            unset($step);
        }

        return $backtrace;
    }

    /**
     * Sets the User object
     * @param object $user
     * @return void
     */
    public function setUserEngine(iUser $user) { $this->user = $user; }

    /**
     * Sets the Database Engine and enables database logging
     * @param object $db
     * @return void
     */
    public function setDBEngine(iDatabaseHandler $db)
    {
        $this->logToDB = true;
        $this->db = $db;
    }

    /**
     * Displays a fatal error
     * @return void
     */
    public function display($message = '', $code = 500,  $errorTemplate = 'main/http-error')
    {
        $mainHeader = (!isset($this->httpHeaders[$code]) ? $this->httpHeaders[500] : $this->httpHeaders[$code]);
        $lang     = new Lang($this->config, $this->hooks);
        $template = new TemplateHandler($this->config, $this->user, $lang, $this->session, $this->hooks);
        $message  = ($lang->exists($message) ? $lang->get($message) : $message);

        $template->load($errorTemplate);
        $template->setHtmlTitle($this->config->get('siteTitle') . ' - Oops! Error!');
        $template->allowHtmlIndexing(false);
        $template->set('message', $message);
        $template->set('code', $code);

        header($mainHeader);
        header('Expires: Mon, 20 Jan 1982 04:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        $template->display();
        die();
    }
}
?>
