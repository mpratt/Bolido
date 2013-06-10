<?php
/**
 * Random.php
 * This class is used to generate random values
 *
 * @package Module.Main.Models
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Random
{
    /**
     * Generates Random Bytes
     *
     * @param string $length
     * @return string
     *
     * @throws InvalidArgumentException when an invalid length is specified.
     * @codeCoverageIgnore
     */
    public function bytes($length = 0)
    {
        if ($length < 1)
            throw new \InvalidArgumentException('The lenght parameter must be a number greater than 0!');

        if (function_exists('openssl_random_pseudo_bytes'))
        {
            $bytes = openssl_random_pseudo_bytes($length, $secure);
            if ($secure === true)
                return $bytes;
        }

        /**
         * There is a bug in mcrypt_create_iv in older versions/snaps of PHP in windows
         * where an empty value is returned.
         * https://bugs.php.net/bug.php?id=55169
         */
        if (function_exists('mcrypt_create_iv') && (strtolower(substr(PHP_OS, 0, 3)) !== 'win' || version_compare(PHP_VERSION, '5.3.7') >= 0))
        {
            $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if ($bytes !== false)
                return $bytes;
        }

        $bytes = '';
        for ($i = 0; $i < $length; $i++)
            $bytes .= chr(mt_rand(0, 255));

        return $bytes;
    }

    /**
     * Generates a random integer between $min and $max
     *
     * @param  int $min
     * @param  int $max
     * @param  int $seedLenght
     * @return int
     *
     * @throws InvalidArgumentException when min/max numbers are invalid
     * @throws RuntimeException when the range is too big.
     */
    public function range($min, $max, $seedLenght = 3)
    {
        if ($min >= $max)
            throw new \InvalidArgumentException('The min parameter must be lower than max parameter');

        $range = $max - $min;
        if ($range > PHP_INT_MAX || is_float($range))
            throw new \RuntimeException('The supplied range is too big');

        $seed = hexdec(bin2hex($this->bytes($seedLenght)));
        while ($seed > 1)
            $seed *= 0.1;

        return (int) $min + ($seed * ($range + 1));
    }

    /**
     * Generates a random string
     *
     * @param  int $length
     * @param  string $chars If no chars are given, it uses Base 64 character set.
     * @return string
     *
     * @throws InvalidArgumentException when an invalid length is specified.
     */
    public function string($length = 0, $chars = null)
    {
        if ($length < 1)
            throw new \InvalidArgumentException('The lenght parameter must be a number greater than 0!');

        if (empty($chars))
        {
            $numBytes = ceil($length * 0.75);
            $bytes    = $this->bytes($numBytes);
            return substr(rtrim(base64_encode($bytes), '='), 0, $length);
        }

        $listLen = strlen($chars);
        if ($listLen == 1)
            return str_repeat($chars, $length);

        $bytes  = $this->bytes($length);
        $pos    = 0;
        $result = '';
        for ($i = 0; $i < $length; $i++)
        {
            $pos = ($pos + ord($bytes[$i])) % $listLen;
            $result .= $chars[$pos];
        }

        return $result;
    }

    /**
     * Generates random boolean
     *
     * @return bool
     */
    public function bool()
    {
        $byte = $this->bytes(1, false);
        return (bool) (ord($byte) % 2);
    }
}
?>
