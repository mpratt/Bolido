<?php
/**
 * TwigParser.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Parser;

use Bolido\Config;
use \Twig_Environment;
use \Twig_Loader_Chain;
use \Twig_Loader_String;
use \Twig_Loader_Filesystem;
use Bolido\Filesystem\Resource;

/**
 * Parser of Twig files
 */
class TwigParser implements ParserInterface
{
    /** @var object Twig Parser */
    protected $parser;

    /**
     * Construct
     *
     * @param object $config
     * @return void
     */
    public function __construct(Config $config)
    {
        $this->twig = new Twig_Environment(
            new Twig_Loader_Chain(
                array(
                    new Twig_Loader_Filesystem($config['layout_dir']),
                    new Twig_Loader_String()
                )
            )
        );
    }

    /** inline {@inheritdoc} */
    public function parseResource(Resource $resource, array $variables = array())
    {
        $tpl = $this->twig->loadTemplate($resource->getBasename());
        return $tpl->render($variables);

    }

    /** inline {@inheritdoc} */
    public function parseString($string, array $variables = array())
    {
        if (preg_match('~\s*{%\s*extend~Asi', $string)) {
            return $this->twig->render($string, $variables);
        } else if (!empty($variables['layout'])) {
            $variables = array_merge(array('block' => 'content'), $variables);
            $template = "{% extends \"$variables[layout]\" %}{% block $variables[block] %}$string{% endblock %}";
        } else {
            $template = $string;
        }

        return $this->twig->render($template, $variables);
    }
}

?>
