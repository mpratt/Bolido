<?php
/**
 * UrlParser.class.php
 * This class extracts important stuff from the url
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
    public function __construct($uri, $config)
    {
        $this->config  = $config;
        $this->mainUrl = parse_url($this->config->get('mainurl'));
        $this->uri     = parse_url(str_ireplace('/index.php', '/', $uri));

        if ($this->uri === false)
            redirectTo($this->config->get('mainurl'));

        // Strip parts from the path that we dont need
        if (!empty($this->mainUrl['path']) && !empty($this->uri['path']))
            $this->uri['path'] = str_ireplace(trim($this->mainUrl['path'], '/'), '', $this->uri['path']);

         if (empty($this->uri['path']))
            $this->uri['path'] = '/';
    }

    /**
     * Tries to correct the a url
     * - If the url has no query and does not end in /
     * - If the mainurl has a www and the current url doesnt
     *
     * @return void
     */
    public function validateUrlConsistency()
    {
        if ((empty($this->uri['query']) && substr($this->uri['path'], -1) != '/') ||
            (stripos($this->config->get('mainurl'), '://www.') !== false && stripos($_SERVER['HTTP_HOST'], 'www.') === false))
        {
            $url = trim($this->config->get('mainurl'), '/') . '/';

            if (!empty($this->uri['path']) && $this->uri['path'] != '/')
                $url .= trim($this->uri['path'], '/') . '/';

            if (!empty($this->uri['query']))
                $url .= '?' . $this->uri['query'];

            redirectTo($url, true);
        }
    }

    /**
     * Strips unimportant stuff from the $uri
     *
     * @param string $uri
     * @return string
     */
    public function getPath()
    {
        return $this->detectLanguage($this->uri['path']);
    }

    /**
     * It detects a language modifier, strips it from the path
     * and validates that the language is allowed.
     *
     * @param string $path
     * @return string
     */
    public function detectLanguage($path)
    {
        if (!empty($this->uri['path']) && preg_match('~^/([a-z]{2})/~i', $this->uri['path'], $matches))
        {
            $lang = trim($matches['1'], '/');
            $path = preg_replace('~^/'.  $lang . '/?~i', '', $this->uri['path']);

            // Is that language allowed in the url?
            if (!in_array($lang, $this->config->get('allowedLanguages')) || $lang == $this->config->get('language'))
                redirectTo($this->config->get('mainurl') . '/' . trim($path, '/') . (!empty($this->uri['query']) ? '?' . $this->uri['query'] : ''));

            $this->config->set('language', $lang);
            $this->config->set('mainurl', $this->config->get('mainurl') . '/' . $lang);
        }

        return $path;
    }

    /**
     * Define the canonical url for this request
     *
     * @return void
     */
    public function defineCanonical()
    {
        // Normalize query-string and remove important/secret stuff from it, mainly for the canonical url
        $query = array();
        if (!empty($this->uri['query']))
        {
            parse_str($this->uri['query'], $query);
            foreach (array('token', 'bolidosessid', 'phpsessid', strtolower(session_name())) as $key)
            {
                if (!empty($query[strtolower($key)]))
                    unset($query[$key]);
            }
        }

        // Define the canonical URL
        $canonical = trim($this->config->get('mainurl'), '/') . '/';

        if (!empty($this->uri['path']) && $this->uri['path'] != '/')
            $canonical .= trim($this->uri['path'], '/') . '/';

        if (!empty($query))
            $canonical .= '?' . http_build_query($query);

        define('CANONICAL_URL', $canonical);
    }
}
?>
