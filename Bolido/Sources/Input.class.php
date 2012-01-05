<?php
/**
 * Input.class.php, validation and input class
 * The Input class automatically handles all the input sent via $_GET, $_POST, $_COOKIE AND $_REQUEST.
 * Superglobal Values are stored in properties and then are unset (except for $_SESSION). This class has many useful
 * validation functions.
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

class Input
{
    protected static $data = array();

    /**
     * Construct
     *
     * @param bool $overwrite_superglobals Disables the use of superglobal POST, GET, COOKIE and FILES
     * @return void
     */
    public function __construct($overwriteGlobals = true)
    {
        if (empty(self::$data))
        {
            self::$data['post']    = $this->stripslashesRecursive($_POST);
            self::$data['cookie']  = $this->stripslashesRecursive($_COOKIE);
            self::$data['files']   = $this->stripslashesRecursive($_FILES);
            self::$data['get']     = $this->stripslashesRecursive($_GET);
            self::$data['request'] = array_merge(self::$data['get'], self::$data['post']);

            if ($overwriteGlobals)
                $GLOBALS['_POST'] = $GLOBALS['_GET'] = $GLOBALS['_COOKIE'] = $GLOBALS['_FILES'] = $GLOBALS['_REQUEST'] = null;
        }
    }

    /**
     * Stripslashes recursively
     *
     * @param array $array
     * @return array Cleaned array
     */
    private function stripslashesRecursive($array = array())
    {
        if (!is_array($array))
            return stripslashes($array);

        if (!empty($array))
        {
            foreach ($array as $k => $v)
                $array[$k] = $this->stripslashesRecursive($v);
        }

        return $array;
    }

    /**
     * Generic getter and has<Variable> method.
     *
     * @param string $method     Name of the called method
     * @param array  $parameters Parameter array
     * @return mixed
     *
     * @examples:
     * $this->hasPost($key);
     * $this->post($key);
     *
     */
    public function __call($method, $parameters)
    {
        if (substr($method, 0, 3) == 'has')
            $variable = substr($method, 3);
        else
            $variable = $method;

        $variable = strtolower($variable);
        if (!in_array($variable, array('get', 'post', 'cookie', 'request', 'files')))
            throw new Exception('Unknown input type! It should be get, post, cookie, request, files');

        if (empty($parameters[0]))
            return self::$data[$variable];

        if (substr($method, 0, 3) == 'has')
            return isset(self::$data[$variable][$parameters[0]]);

        if (!isset(self::$data[$variable][$parameters[0]]))
            return '';

        return self::$data[$variable][$parameters[0]];
    }

    /**
     * For Debugging Only
     *
     * @return string
     */
    public function __toString() { return print_r(self::$data, true); }
}
?>