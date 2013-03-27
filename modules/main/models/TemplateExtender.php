<?php
/**
 * TemplateExtender.php
 * A bunch of methods used to extend the functionality
 * of the template object. This class is mostly used
 * to append data to the header and footer.
 *
 * In order to work this class needs that the template files have
 * a $toHeader and $toFooter variables defined.
 *
 * @package This file is part of the Bolido Framework
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

class TemplateExtender
{
    protected $config;
    protected $lang;

    protected $hasTitle = false;
    protected $hasDescription = false;
    protected $toHeader = array();
    protected $toFooter = array();
    protected $records  = array();

    /**
     * The Session engine.
     *
     * @param object $config
     * @param object $lang
     * @return void
     */
    public function __construct(\Bolido\Adapters\BaseConfig $config, \Bolido\Lang $lang)
    {
        $this->config = $config;
        $this->lang = $lang;
    }

    /**
     * Actually appends the values to the Template
     *
     * @param object $template
     * @return void
     */
    public function appendToTemplate($template)
    {
        ksort($this->toHeader);
        ksort($this->toFooter);

        $template->set('toHeader', $this->toHeader, true);
        $template->set('toFooter', $this->toFooter, true);
    }

    /**
     * Appends a value to the header property
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
     * Appends a value to the footer property
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
     * Appends a value to an array based on its priority.
     * This object only appends unique code strings.
     *
     * @param array  $section    The array to be modified
     * @param string $code       The value to be added
     * @param int    $priority   The priority defines the position where
     *                           the code should be appended. A '+' sign
     *                           just tells the method to append. This param
     *                           also accepts negative numbers.
     * @return array
     */
    protected function appendTo(array $section, $code, $priority = '+')
    {
        if (!isset($this->records[md5($code)]))
        {
            if ($priority == '-1')
                array_unshift($section, $code);
            else if (is_numeric($priority))
            {
                if (!empty($section[$priority]))
                    return $this->appendTo($section, $code, ++$priority);
                else
                    $section[$priority] = $code;
            }
            else
                $section[] = $code;

            $this->records[md5($code)] = true;
        }

        return $section;
    }

    /**
     * Checks if an html title was already defined
     *
     * @return bool
     */
    public function hasTitle() { return (bool) $this->hasTitle; }

    /**
     * Checks if an html description was already defined
     *
     * @return bool
     */
    public function hasDescription() { return (bool) $this->hasDescription; }

    /**
     * Sets the Title tag for HTML pages
     *
     * @param string $title
     * @param bool   $appendSiteTitle  When true, appends the siteTitle found on the
     *                                 config object.
     * @return void
     */
    public function setHtmlTitle($title = '', $appendSiteTitle = true)
    {
        if ($this->hasTitle)
            return ;

        $title = $this->lang->get($title);
        if ($appendSiteTitle)
            $htmlTitle = trim($this->config->siteTitle . ' - ' . $title);
        else
            $htmlTitle = $title;

        $htmlTitle = htmlspecialchars($htmlTitle, ENT_QUOTES, 'UTF-8', false);
        $this->appendToHeader(sprintf('<title>%s</title>', trim($htmlTitle, ' -')), '-1');
        $this->hasTitle = true;
    }

    /**
     * Sets the Description tag for HTML pages
     *
     * @param string $htmlDescription
     * @return void
     */
    public function setHtmlDescription($htmlDescription = '')
    {
        if ($this->hasDescription)
            return ;

        $htmlDescription = strip_tags($this->lang->get($htmlDescription));
        if (strlen($htmlDescription) > 500)
            $htmlDescription = substr($htmlDescription, 0, strpos($htmlDescription, ' ', 500)) . '...';

        $htmlDescription = htmlspecialchars($htmlDescription, ENT_QUOTES, 'UTF-8', false);
        $this->appendToHeader(sprintf('<meta name="description" content="%s">', $htmlDescription), '-1');
        $this->hasDescription = true;
    }

    /**
     * Allows or dissallows crawlers to index the current page
     *
     * @param bool $allow
     * @return void
     */
    public function allowHtmlIndexing($allow = true)
    {
        if (!$allow)
            $this->appendToHeader('<meta name="robots" content="noindex, nofollow, noimageindex, noarchive">', '-1');
    }

    /**
     * Method that appends a css url to the template header.
     *
     * @param string $css
     * @param int $priority
     * @return void
     */
    public function css($css, $priority = '+')
    {
        $this->appendToHeader(sprintf('<link rel="stylesheet" href="%s" type="text/css">', $css), $priority);
    }

    /**
     * Method that appends javascript url to the template header.
     *
     * @param string $javascript
     * @param int $priority
     * @return void
     */
    public function js($javascript, $priority = '+')
    {
        $this->appendToHeader(sprintf('<script type="text/javascript" src="%s"></script>', $javascript), $priority);
    }

    /**
     * Method that appends javascript to the template footer.
     *
     * @param string $javascript
     * @param int $priority
     * @return void
     */
    public function fjs($javascript, $priority = '+')
    {
        $this->appendToFooter(sprintf('<script type="text/javascript" src="%s"></script>', $javascript), $priority);
    }

    /**
     * Method that appends inline javascript to the template.
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
     * Method that appends inline javascript to the template footer.
     *
     * @param string $javascript
     * @param int $priority
     * @return void
     */
    public function fijs($javascript, $priority = '+')
    {
        $this->appendToFooter('<script type="text/javascript">' . trim($javascript) . '</script>', $priority);
    }
}
?>
