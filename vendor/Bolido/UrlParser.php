<?php
/**
 * UrlParser.php
 * This class extracts important stuff from the url
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

class UrlParser
{
    protected $config;
    protected $mainUrl;
    protected $uri;

    /**
     * Construct
     *
     * @param string $uri
     * @param object $config
     * @return void
     */
    public function __construct($uri, \Bolido\Adapters\BaseConfig $config)
    {
        $this->config  = $config;
        $this->mainUrl = parse_url($this->config->mainUrl);
        $this->uri     = parse_url(str_ireplace('/index.php', '/', $uri));

        if ($this->uri === false)
            throw new \Exception('Invalid request uri given!');

        if (empty($this->uri['path']))
            $this->uri['path'] = '/';

        // Strip parts from the path that we dont need
        if (!empty($this->mainUrl['path']))
            $this->uri['path'] = '/' . ltrim(str_replace(trim($this->mainUrl['path'], '/'), '', $this->uri['path']), '/');

        if (!empty($this->uri['query']))
        {
            $query = array();
            parse_str($this->uri['query'], $query);
            $this->uri['query'] = $query;
        }
    }

    /**
     * Tries to correct the url
     * - If the url specifies an invalid language locale.
     * - If the url has no query and does not end in /
     * - If the mainurl has a www and the current url doesnt
     *
     * @return void
     */
    public function urlNotConsistent()
    {
        return (substr($this->uri['path'], -1) != '/' ||
                !$this->isLanguageAllowed() ||
                (
                    isset($_SERVER['HTTP_HOST']) &&
                    stripos($this->config->mainUrl, '://www.') !== false &&
                    stripos($_SERVER['HTTP_HOST'], 'www.') === false
                )
        );
    }

    /**
     * Checks if a specified language locale was given
     *
     * @return bool
     */
    public function isLanguageAllowed()
    {
        if (!empty($this->uri['query']['locale']))
        {
            return ($this->uri['query']['locale'] != $this->config->language &&
                    in_array($this->uri['query']['locale'], $this->config->allowedLanguages));
        }

        return true;
    }

    /**
     * Strips unimportant stuff from the $uri
     *
     * @param string $uri
     * @return string
     */
    public function getPath() { return rtrim($this->uri['path'], '/') . '/'; }

    /**
     * Extract the canonical url for this request
     *
     * @return string
     */
    public function getCanonical()
    {
        $query = array();
        if (!empty($this->uri['query']))
        {
            if (!$this->isLanguageAllowed())
                unset($this->uri['query']['locale']);

            foreach (array('token', 'BOLIDOSESSID', 'PHPSESSID', session_name()) as $key)
            {
                if (!empty($this->uri['query'][$key]))
                    unset($this->uri['query'][$key]);
            }
        }

        $canonical = preg_replace('~//$~', '/', $this->config->mainUrl . $this->uri['path'] . '/');
        if (!empty($this->uri['query']))
            $canonical .= '?' . http_build_query($this->uri['query']);

        return $canonical;
    }
}
?>
