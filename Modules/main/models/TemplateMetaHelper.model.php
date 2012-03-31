<?php
/**
 * TemplateMetaHelper.model.php
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

class TemplateMetaHelper
{
    protected $session;
    protected $hooks;
    protected $config;

    protected $toHeader = array();
    protected $toFooter = array();
    protected $appendPriority = '1000';

    /**
     * The Session engine.
     *
     * @param object $config
     * @return void
     */
    public function setConfigEngine($config)
    {
        $this->config = $config;
    }

    /**
     * The Session engine.
     *
     * @param object $session
     * @return void
     */
    public function setSessionEngine($session)
    {
        $this->session = $session;
    }

    /**
     * The Hooks engine.
     *
     * @param object $hooks
     * @return void
     */
    public function setHooksEngine($hooks)
    {
        $this->hooks = $hooks;
        $this->hooks->append(array('from_module' => 'main',
                                   'call' => array($this, 'appendToTemplate')), 'before_template_body_generation');
    }

    /**
     * Appends values to the Template
     *
     * @param object $template
     * @return void
     */
    public function appendToTemplate($template)
    {
        $this->hooks->run('template_append_to_meta_helper', $this);
        ksort($this->toHeader);
        ksort($this->toFooter);

        $template->set('toHeader', $this->toHeader, true);
        $template->set('toFooter', $this->toFooter, true);
    }

    /**
     * Appends a value to the header
     *
     * @param string $code
     * @param int $priority
     * @return void
     */
    public function appendToHeader($code, $priority = '+')
    {
        $this->toHeader = $this->appendTo($this->toHeader, $code, $priority);
    }

    /**
     * Appends a value to the footer
     *
     * @param string $code
     * @param int $priority
     * @return void
     */
    public function appendToFooter($code, $priority = '+')
    {
        $this->toFooter = $this->appendTo($this->toFooter, $code, $priority);
    }

    /**
     * Appends a value to an array based on its priority
     *
     * @param array $section The array to be modified
     * @param string $code The value to be added
     * @param int $priority
     * @return array
     */
    protected function appendTo($section, $code, $priority)
    {
        if (!is_array($section))
            $section = array();

        if ($priority == '-1')
            array_unshift($section, $code);
        else if (is_numeric($priority))
        {
            if (!empty($section[$priority]))
            {
                while (!empty($section[$this->appendPriority]))
                    $this->appendPriority++;

                $section[$this->appendPriority] = $code;
            }
            else
                $section[$priority] = $code;
        }
        else
            $section[] = $code;

        return $section;
    }

    /**
     * Sets the Title tag for HTML pages
     *
     * @param string $title
     * @param bool $appendSiteTitle When true appends the site title
     * @return void
     */
    public function setHtmlTitle($title = '', $appendSiteTitle = true)
    {
        if (!empty($title))
        {
            if ($appendSiteTitle)
                $htmlTitle = $this->config->get('siteTitle') . ' - ' . $title;
            else
                $htmlTitle = $title;
        }
        else
            $htmlTitle = $this->config->get('siteTitle');

        $htmlTitle = htmlspecialchars($htmlTitle, ENT_QUOTES, 'UTF-8', false);
        $this->appendToHeader(sprintf('<title>%s</title>', $htmlTitle), '-1');
    }

    /**
     * Sets the Description tag for HTML pages
     *
     * @param string $htmlDescription
     * @return void
     */
    public function setHtmlDescription($htmlDescription = '')
    {
        $htmlDescription = strip_tags($htmlDescription);
        if (strlen($htmlDescription) > 500)
            $htmlDescription = substr($htmlDescription, 0, strpos($htmlDescription, ' ', 500)) . '...';

        $htmlDescription = htmlspecialchars($htmlDescription, ENT_QUOTES, 'UTF-8', false);
        $this->appendToHeader(sprintf('<meta name="description" content="%s">', $htmlDescription), '-1');
    }

    /**
     * Allows or dissallows crawlers to index the current page
     *
     * @param bool $allow
     * @return void
     */
    public function allowHtmlIndexing($allow = true)
    {
        if ($allow === false)
            $this->appendToHeader('<meta name="robots" content="noindex, nofollow, noimageindex, noarchive" />', '-1');
    }

    /**
     * Method that append css to the template header.
     *
     * @param string $url
     * @param int $priority
     * @return void
     */
    public function css($css, $priority = '+')
    {
        $css = $this->normalize($css);
        $this->appendToHeader(sprintf('<link rel="stylesheet" href="%s" type="text/css" />', $css), $priority);
    }

    /**
     * Method that append javascript to the template header.
     *
     * @param string $url
     * @param int $priority
     * @return void
     */
    public function js($javascript, $priority = '+')
    {
        $javascript = $this->normalize($javascript);
        $this->appendToHeader(sprintf('<script type="text/javascript" src="%s"></script>', $javascript), $priority);
    }

    /**
     * Method that append javascript to the template footer.
     *
     * @param string $url
     * @param int $priority
     * @return void
     */
    public function fjs($javascript, $priority = '+')
    {
        $javascript = $this->normalize($javascript);
        $this->appendToFooter(sprintf('<script type="text/javascript" src="%s"></script>', $javascript), $priority);
    }

    /**
     * Method that append inline javascript to the template.
     *
     * @param string $javascript
     * @param int $priority
     * @return void
     */
    public function ijs($javascript, $priority = '+')
    {
        $this->appendToHeader('<script type="text/javascript">' . trim($javascript) . '</script>', $priority);
    }

    /**
     * Method that append inline javascript to the template footer.
     *
     * @param string $javascript
     * @param int $priority
     * @return void
     */
    public function fijs($javascript, $priority = '+')
    {
        $this->appendToFooter('<script type="text/javascript">' . trim($javascript) . '</script>', $priority);
    }

    /**
     * Normalize a url for scripts
     *
     * @param string $url
     * @return string
     */
    protected function normalize($url)
    {
        if (strpos($url, '{|placeholder|}') !== false)
            throw new Exception('You cannot use the word {|placeholder|} inside your code');

        $url = trim($url);
        if (defined('IN_DEVELOPMENT') && IN_DEVELOPMENT)
        {
            if (strpos($url, '?') !== false)
                $url .= '&bolidoNoCacheRandNumber=' . time();
            else
                $url .= '?' . time();
        }

        return $url;
    }
}
?>
