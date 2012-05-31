<?php
/**
 * Session.class.php
 * This class wraps the $_SESSION superglobal
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

class Session
{
    protected $name    = 'BOLIDOSESSID';
    protected $started = false;
    protected $hooks;
    protected $handler;

    /**
     * Constructs the session object.
     *
     * @param object $config
     * @param object $hooks
     * @return void
     */
    public function __construct(iConfig $config, Hooks $hooks)
    {
        $this->hooks = $hooks;
        if ($this->started)
            return ;

        @ini_set('session.use_trans_sid', false);
        @ini_set('session.use_cookies', true);
        @ini_set('session.use_only_cookies', true);
        @ini_set('url_rewriter.tags', '');
        @ini_set('arg_separator.output', '&amp;');
        @ini_set('session.gc_probability', '40');

        $domain = $config->get('httpDomain');
        if (!empty($domain))
        {
            @ini_set('session.cookie_domain', '.' . $domain);
            session_set_cookie_params(0, '/', '.' . $domain);
        }
    }

    /**
     * Sets a session variable.
     *
     * @param mixed $key   Session variable name
     * @param mixed $value Session variable value
     * @return null
     */
    public function set($key, $value) { $_SESSION[$key] = $value; }

    /**
     * Returns a session variable.
     *
     * @param mixed $key   Session variable name
     * @return mixed Session variable value Or False on error
     */
    public function get($key)
    {
        if (!$this->has($key))
            return false;

        return $_SESSION[$key];
    }

    /**
     * Unsets a Session Key
     *
     * @param mixed $key Session variable name
     * @return bool
     */
    public function delete($key)
    {
        if ($this->has($key))
            unset($_SESSION[$key]);
    }

    /**
     * Checks whether a session variable exists.
     *
     * @param mixed $key Session variable name
     * @return bool
     */
    public function has($key) { return isset($_SESSION[$key]); }

    /**
     * Resets all the session values
     * @return void
     */
    public function reset() { $_SESSION = array(); }

    /**
     * Check whether the session has already been started.
     *
     * @return bool
     */
    public function isStarted() { return $this->started; }

    /**
     * Starts the sesssion.
     *
     * @return bool
     */
    public function start()
    {
        if ($this->started)
            return false;

        $this->hooks->run('before_session_start');
        session_name($this->name);
        if (session_start())
        {
            $this->started = true;
            return true;
        }

        return false;
    }

    /**
     * Sets the session name.
     *
     * @param string $name Session name
     * @return void
     */
    public function setName($name)
    {
        if (!$this->started)
            $this->name = $name;
    }

    /**
     * Returns the session name.
     *
     * @return string
     */
    public function getName() { return $this->name; }

    /**
     * Returns the sesssion id.
     *
     * @return mixed False on Error
     */
    public function getId()
    {
        if (!$this->started)
            return false;

        return session_id();
    }

    /**
     * Regenerate session id to make session fixation harder.
     *
     * @param bool $deletePrevious Wether the previous session should be deleted
     * @return mixed False on error
     */
    public function regenerateId($deletePrevious = false)
    {
        if (!$this->started)
            return false;

        session_regenerate_id($deletePrevious);
    }

    /**
     * Stores the session data and closes the session
     *
     * @return null
     */
    public function close() { session_write_close(); }

    /**
     * Destroy the session.
     *
     * @return null false on error
     */
    public function destroy()
    {
        if (!$this->started)
            return false;

        $this->reset();
        session_destroy();

        $this->started = false;
        setcookie($this->name, '', time() - 42000);
    }
}
?>
