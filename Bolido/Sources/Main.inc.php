<?php
/**
 * Main.inc.php
 * Important functions that need to be loaded with the Framework
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

/**
 * The allmighty autoload function
 *
 * @param string $classname The name of the class that is needed
 * @return bool
 */
function bolidoAutoload($classname)
{
    $sourceDir = dirname(__FILE__);
    if (is_readable($sourceDir . '/' . $classname . '.class.php'))
        return require($sourceDir . '/' . $classname . '.class.php');
    else if (is_readable($sourceDir . '/Interfaces/' . $classname . '.interface.php'))
        return require($sourceDir . '/Interfaces/' . $classname . '.interface.php');
    else
        return false;
}

/**
 * Redirects to a url
 *
 * @param string $url The Location to be redirected. When not specified, the main url is assumed
 * @param bool $permanently
 * @return void
 */
function redirectTo($url = '', $permanently = false)
{
    if ($permanently)
        header('HTTP/1.1 301 Moved Permanently');

    if (trim($url) != '')
        header('Location: ' . $url);
    else
        header('Location: /');

    exit;
}

/**
 * Traces the Ip of the current user
 *
 * @return string
 */
function detectIp()
{
    if (!empty($_SERVER['REMOTE_ADDR']) && isIp($_SERVER['REMOTE_ADDR']))
        return $_SERVER['REMOTE_ADDR'];
    else if  (!empty($_SERVER['HTTP_CLIENT_IP']) && isIp($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && isIp($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else
        return '127.0.0.1';
}

/**
 * Traces the hostname of the current user
 *
 * @return string
 */
function detectHostname()
{
    if (!empty($_SERVER['REMOTE_HOST']))
        return $_SERVER['REMOTE_HOST'];
    else if (isIp(detectIp()))
        return gethostbyaddr(detectIp());
    else
        return 'unknown';
}

/**
 * Prepares $text for output.
 * When an array is passed, it applies recursively.
 *
 * @param mixed $value The string or array that is going to be prepared for output
 * @param bool $allowHtml Tells the method wether to use htmlentities on $text. By default false.
 * @param string $charset The charset of the string
 * @return void
 */
function prepareOutput($value = '', $allowHtml = false, $charset = 'UTF-8')
{
    if (!is_array($value))
    {
        if (!$allowHtml)
            $value = htmlspecialchars($value, ENT_QUOTES, $charset, false);

        return stripslashes($value);
    }

    foreach ($value as $k => $v)
             $value[$k] = prepareOutput($v, $allowHtml, $charset);

    return $value;
}

/**
 * Strips non-url friendly characters from $string
 *
 * @param string $url the url to be checked
 * @return string cleaned url
 */
function prepareUrl($url = '')
{
    if (function_exists('iconv'))
        $url = iconv('UTF-8', 'US-ASCII//TRANSLIT', $url);

    return preg_replace('~[^a-z0-9\.\\\:]|\s+~i', '-', $url);
}

/**
 * Checks if the $ip is an ip.
 *
 * @param string $ip
 * @return bool
 */
function isIp($ip) { return (bool) filter_var($ip,FILTER_VALIDATE_IP); }

/**
 * Checks if the $date matches a MySQL date format (YYYY-MM-DD)
 *
 * @param string $date
 * @return bool
 */
function isSqlDate($date) { return (bool) preg_match('~^(\d{4})-(\d{2})-(\d{2})$~', $date); }

/**
 * Checks if the $date matches a MySQL date-time format (YYYY-MM-DD HH:MM:SS)
 *
 * @param string $date
 * @return bool
 */
function isSqlDateTime($date) { return (bool) preg_match('~^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$~', $date); }

/**
 * Checks if the $email is a email address
 *
 * @param string $email
 * @return bool
 */
function isEmail($email) { return filter_var($email, FILTER_VALIDATE_EMAIL); }

/**
 * Checks if the $url is a url
 *
 * @param string $url
 * @return bool
 */
function isUrl($url)
{
    if (strlen($url) < 3 || !filter_var($url, FILTER_VALIDATE_URL))
        return false;

    $check = @parse_url($url);
    return (is_array($check) && isset($check['scheme']) && isset($check['host']) && count(explode('.', $check['host'])) > 1);
}

?>
