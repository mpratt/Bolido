<?php
/**
 * Functions.php
 * Important functions that need to be loaded with the Framework
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
 * Redirects to a url
 *
 * @param string $url The Location to be redirected. When not specified, the root is assumed
 * @param bool $permanently Wheter or not to send a 301 header.
 * @return void
 *
 * @throws InvalidArgumentException when the redirection is not possible.
 */
function redirectTo($url = '', $permanently = false)
{
    if (trim($url) == '')
        $url = '/';

    // @codeCoverageIgnoreStart
    if (!headers_sent())
    {
        if ($permanently)
            header('HTTP/1.1 301 Moved Permanently');

        header('Location: ' . $url);
        die();
    }
    // @codeCoverageIgnoreEnd

    throw new \InvalidArgumentException('Problem redirecting to ' . $url . ' , headers have been sent already');
}

/**
 * Traces the Ip of the current user
 *
 * @return string
 */
function detectIp()
{
    if (!empty($_SERVER['REMOTE_ADDR']))
        return $_SERVER['REMOTE_ADDR'];
    else
        return null;
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

    return detectIp();
}

/**
 * Prepares $text for output.
 * When an array is passed, it applies recursively.
 *
 * @param mixed $value The string or array that is going to be prepared for output
 * @param bool $allowHtml Tells the method wether to use htmlspecialchars on the text.
 * @param string $charset The charset encoding of the string
 * @return void
 */
function sanitize($value, $allowHtml = false, $charset = 'UTF-8')
{
    if (!is_array($value))
    {
        if (!$allowHtml)
            $value = htmlspecialchars($value, ENT_QUOTES, $charset, false);

        return stripslashes($value);
    }

    foreach ($value as $k => $v)
             $value[$k] = sanitize($v, $allowHtml, $charset);

    return $value;
}

/**
 * Strips non-url friendly characters from $string
 *
 * @param string $url
 * @param array  $removeList An array with words or characters you want to strip.
 * @param int    $lenght The lenght of the resulting string. 0 disables it.
 * @param bool   $spaceToHyphen Convert spaces to hyphens.
 * @return string cleaned url
 */
function urlize($url = '', $removeList = array(), $lenght = 0, $spaceToHyphen = true)
{
    $latinChars = array('À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
             			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
              			'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
			            'Ő' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U',
			            'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
			            'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
			            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
			            'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
			            'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y', '©' => '(c)');

    $greekChars = array('α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			            'Γ' => 'G', 'Δ' => 'D', 'Θ' => '8',
			            'Λ' => 'L', 'Ξ' => '3', 'Π' => 'P',
			            'Σ' => 'S', 'Φ' => 'F', 'Ψ' => 'PS', 'Ω' => 'W',
			            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			            'Ϋ' => 'Y');

    $turkishChars = array('ş' => 's', 'Ş' => 'S', 'ı' => 'i', 'İ' => 'I', 'ç' => 'c', 'Ç' => 'C', 'ü' => 'u', 'Ü' => 'U',
                          'ö' => 'o', 'Ö' => 'O', 'ğ' => 'g', 'Ğ' => 'G');

    $russianChars = array('в' => 'v', 'г' => 'g', 'д' => 'd', 'ё' => 'yo', 'ж' => 'zh',
			              'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
               	    	  'п' => 'p', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			              'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
                		  'я' => 'ya',
                		  'Б' => 'B', 'Г' => 'G', 'Д' => 'D', 'Ё' => 'Yo', 'Ж' => 'Zh',
			              'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'Н' => 'N', 'О' => 'O',
			              'П' => 'P', 'Т' => 'T', 'Ф' => 'F', 'Ц' => 'C',
			              'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
                          'Я' => 'Ya');

    $ukranianChars = array('Є' => 'Ye', 'Ї' => 'Yi', 'Ґ' => 'G', 'є' => 'ye', 'ї' => 'yi', 'ґ' => 'g');

	$czechChars = array('č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		                'ž' => 'z', 'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T',
			            'Ů' => 'U', 'Ž' => 'Z');

    $polishChars = array('ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			             'ż' => 'z', 'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S',
			             'Ź' => 'Z', 'Ż' => 'Z');

    $latvianChars = array ('ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
              			   'š' => 's', 'ū' => 'u', 'ž' => 'z', 'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i',
                           'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z');

    $charMap = array($latinChars, $greekChars, $turkishChars, $russianChars, $ukranianChars, $czechChars, $polishChars, $latvianChars);

    // "transliterate" the string and strip non words and spaces
    foreach ($charMap as $m)
    {
        $url = str_replace(array_keys($m), array_values($m), $url);
    }

    $url = preg_replace('~[^-\w\s]~', '', $url);
    if (empty($url))
        return '';

    if (!empty($removeList))
        $url = preg_replace('~\b(' . implode('|', $removeList) . ')\b~i', '', $url);

    if (is_numeric($lenght) && $lenght > 0 && strlen($url) > $lenght)
        $url = substr($url, 0, $lenght);

    //if (function_exists('iconv'))
        //$url = iconv('UTF-8', 'US-ASCII//TRANSLIT', $url);

    $url = trim($url);
    if ($spaceToHyphen)
        $url = preg_replace('/[-\s]+/', '-', $url);

    return $url;
}

?>
