<?php
/**
 * Extension.php
 *
 * @package Bolido.TwigExtension
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Twig;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class Extension extends \Twig_Extension
{
    protected $app;

    /**
     * Construct
     *
     * @param object $app
     * @return void
     */
    public function __construct(\Bolido\Container $app) { $this->app = $app; }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('lang', array($this->app['lang'], 'get'))
        );
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('lang', array($this->app['lang'], 'get'))
        );
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array
     */
    public function getGlobals()
    {
        $vars = array(
            'config' => $this->app['config'],
            'session' => $this->app['session'],
        );

        if (defined('CANONICAL_URL'))
            $vars = array_merge($vars, array('canonical' => CANONICAL_URL));

        return $vars;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName() { return 'BolidoExtension'; }
}

?>
