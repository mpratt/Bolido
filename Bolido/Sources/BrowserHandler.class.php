<?php
/**
 * BrowserHandler.class.php
 * Inspects the User Agent for general information.
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

class BrowserHandler
{
    protected $userAgent;
    protected $detected = array();

    // Cant touch this!
    const UNKNOWN = 'unknown';
    const IE      = 'internet explorer';
    const OPERA   = 'opera';
    const FIREFOX = 'firefox';
    const SAFARI  = 'safari';
    const CHROME  = 'chrome';
    const KONQUEROR = 'konqueror';
    const NETSCAPE  = 'netscape';

    protected $knownOS = array('win', 'mac', 'linux', 'freebsd', 'openbsd', 'beos', 'os/2');
    protected $knownMobileOS = array('iphone', 'playstation', 'symbian', 'nintendo', 'ipad', 'ipod', 'blackberry',
                                     'android', 'palm', 'samsung', 'sonyericsson', 'smartphone', 'kindle', 'tablet');

    protected $knownEngines = array('presto', 'trident', 'gecko', 'webkit', 'khtml');
    protected $browserList  = array(array('browser' => self::OPERA,
                                          'search_for'    => 'opera',
                                          'version_match' => '~Version/([0-9\.]+)$|Opera /?([0-9]+)~i'),

                                    array('browser' => self::IE,
                                          'search_for'    => 'msie',
                                          'version_match' => '~msie ([0-9\.]+)~i'),

                                    array('browser' => self::FIREFOX,
                                          'search_for'    => '~(?:Firefox|Ice[wW]easel|IceCat)/~',
                                          'version_match' => '~(?:Firefox|Ice[wW]easel|IceCat)/([0-9]+)~'),

                                    array('browser' => self::CHROME,
                                          'search_for'    => 'chrome',
                                          'version_match' => '~chrome/([0-9]+)\.~i'),

                                    array('browser' => self::SAFARI,
                                          'search_for'    => 'safari',
                                          'version_match' => '~safari/([0-9]{1,2}).~i'),

                                    array('browser' => self::KONQUEROR,
                                          'search_for'    => 'konqueror',
                                          'version_match' => '~konqueror/([0-9]+)\.~i'),

                                    array('browser' => self::NETSCAPE,
                                          'search_for'    => 'netscape',
                                          'version_match' => '~netscape/([0-9]+)\.~i')
                                  );

    /**
     * Constructor
     *
     * @param string $userAgent The user agent string
     * @return void
     */
    public function __construct($userAgent = '') { $this->loadUserAgent($userAgent); }

    /**
     * Sets the user agent and triggers the detection process.
     *
     * @param string $userAgent The user agent string
     * @return void
     */
    public function loadUserAgent($userAgent = '')
    {
        $this->userAgent = trim($userAgent);
        $this->detect();
    }

    /**
     * Determines the actual browser name and version
     * @return void
     */
    protected function detect()
    {
        if (isset($this->detected[md5($this->userAgent)]['browser']))
            return ;

        $browser = $version = $engine = self::UNKNOWN;
        if (!empty($this->userAgent))
        {
            foreach ($this->browserList as $b)
            {
                if (strpos($b['search_for'], '~') !== false)
                    $match = (bool) (preg_match($b['search_for'], $this->userAgent) === 1);
                else
                    $match = stripos($this->userAgent, $b['search_for']);

                if ($match !== false)
                {
                    $browser = $b['browser'];
                    if (preg_match($b['version_match'], $this->userAgent, $matches) === 1)
                        $version = $matches['1'];

                    break;
                }
            }

            foreach ($this->knownEngines as $eng)
            {
                if (stripos($this->userAgent, $eng) !== false)
                {
                    $engine = $eng;
                    break;
                }
            }
        }

        $this->detected[md5($this->userAgent)]['browser'] = array('name' => $browser,
                                                                  'version' => $version,
                                                                  'engine' => $engine);

        return ;
    }

    /**
     * Returns the Browser Name
     * @return string
     */
    public function getBrowserName() { return $this->detected[md5($this->userAgent)]['browser']['name']; }

    /**
     * Returns the Browser Version
     * @return string
     */
    public function getBrowserVersion() { return $this->detected[md5($this->userAgent)]['browser']['version']; }

    /**
     * Returns the Browser Engine
     * @return string
     */
    public function getBrowserEngine() { return $this->detected[md5($this->userAgent)]['browser']['engine']; }

    /**
     * Tells if the user is running in a mobile device
     * @return bool
     */
    public function isMobile()
    {
        if (isset($this->detected[md5($this->userAgent)]['is_mobile']))
            return $this->detected[md5($this->userAgent)]['is_mobile'];

        $return = false;
        if (in_array($this->getOS(), $this->knownMobileOS)
            || stripos($this->userAgent, 'opera mini') !== false
            || stripos($this->userAgent, 'opera mobi') !== false
            || stripos($this->userAgent, 'pocket') !== false
            || stripos($this->userAgent, 'mspie') !== false
            || stripos($this->userAgent, 'fennec') !== false
            || stripos($this->userAgent, 'mobile') !== false
            || stripos($this->userAgent, 'wireless') !== false
            || stripos($this->userAgent, 'Series60') !== false
            || stripos($this->userAgent, 'S60') !== false)
        {
            $return = true;
        }

        return $this->detected[md5($this->userAgent)]['is_mobile'] = $return;
    }

    /**
     * Determines if it is a crawler/robot
     * @return boolean true if it is a crawler, false otherwise
     */
    public function isCrawler()
    {
        if (isset($this->detected[md5($this->userAgent)]['is_crawler']))
            return $this->detected[md5($this->userAgent)]['is_crawler'];

        $isCrawler = (bool) (preg_match('~(?:crawl|validator|bloglines|dtaagent|ia_archiver|bot|java|mediapartners|slurp|spider|teoma|ultraseek|waypath|yacy|omniweb|wget)~i', $this->userAgent) === 1);
        return $this->detected[md5($this->userAgent)]['is_crawler'] = $isCrawler;
    }

    /**
     * Tries to gues the Operating system
     * @return string
     */
    public function getOS()
    {
        if (isset($this->detected[md5($this->userAgent)]['os']))
            return $this->detected[md5($this->userAgent)]['os'];

        $os = self::UNKNOWN;
        foreach (array_merge($this->knownOS, $this->knownMobileOS) as $v)
        {
            if (stripos($this->userAgent, $v) !== false)
            {
                $os = $v;
                break;
            }
        }

        return $this->detected[md5($this->userAgent)]['os'] = $os;
    }

    /**
     * For debugging
     * @return string with detected information
     */
    public function __toString() { return print_r($this->detected, true); }
}
?>