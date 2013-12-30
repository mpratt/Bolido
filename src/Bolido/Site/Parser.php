<?php
/**
 * Parser.php
 *
 * @package Bolido
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolido\Site;

use \lessc;
use Ciconia\Ciconia;
use Bolido\Filesystem\Resource;
use Bolido\Outputter\OutputterInterface;

/**
 * Class responsable for file parsing
 */
class Parser
{
    /** @var array Configuration directives */
    protected $config = array();

    /** @var object Implementing OutputterInterface */
    protected $outputter;

    /** @var object Markdown Parser */
    protected $markdown;

    /** @var object LESS Parser */
    protected $less;

    /** @var object Twig Parser */
    protected $twig;

    /**
     * Construct
     *
     * @param array $config
     * @param object $outputter
     * @return void
     */
    public function __construct(array $config, OutputterInterface $outputter)
    {
        $this->config = $config;
        $this->outputter = $outputter;

        /**
         * Instantiate file compilers
         */
        $this->markdown = new Ciconia();
        $this->less = new lessc();
        $this->twig = new \Twig_Environment(
            new \Twig_Loader_Chain(
                array(
                    new \Twig_Loader_Filesystem($this->config['layout_dir']),
                    //new \Twig_Loader_Filesystem(__DIR__ . '/../Layouts'),
                    new \Twig_Loader_String()
                )
            )
        );
    }

    /**
     * Parses the contents of a file
     *
     * @param object $resource
     * @param array $categories
     * @return string
     */
    public function parse(Resource $resource, array $categories = array())
    {
        if ($resource->isMarkdown()) {
            return $this->parseMarkdown($resource, $categories);
        } elseif ($resource->isTwig()) {
            return $this->parseTwigFromString($resource, $categories);
        } else if ($resource->isLess()) {
            return $this->parseLess($resource);
        } else {
            $this->outputter('<error>[Parser]: Unknown parser for</error>: ' . (string) $resource);
            return null;
        }
    }

    /**
     * Gets all the available variables for the twig engine to process.
     *
     * @param object $resource
     * @param array $categories
     * @return array
     */
    protected function getFileVariables(Resource $resource, array $categories = array())
    {
        $globalVars = array();
        $localVars = $resource->getFrontMatter();
        if (!empty($this->config[$resource->getNamespace()])) {
            $globalVars = $this->config[$resource->getNamespace()];
        }

        return array_merge($globalVars, $localVars, $categories);
    }

    /**
     * Parses Markdown files
     *
     * @param object $resource
     * @param array $categories
     * @return string
     */
    protected function parseMarkdown(Resource $resource, array $categories = array())
    {
        $variables = array_merge(array(
            'layout' => 'default.twig',
            'block' => 'content',
        ), $this->getFileVariables($resource, $categories));

        $this->outputter->write(
            '<comment>Parsing Markdown: </comment>' . $resource->getBasename() .
            ' <comment>using layout</comment> ' . $variables['layout'] .
            ' <comment>using block name</comment> "' . $variables['block'] . '"'
        );

        $markdown = $this->markdown->render($resource->getContents());
        if (strtolower($variables['layout']) == 'raw' || preg_match('~\s*{%\s*extend~Asi', $markdown)) {
            $this->outputter->write('<comment>File seems to be a template, falling back to RAW</comment>');
            $template = $markdown;
        } else {
            $template = "{% extends \"$variables[layout]\" %}{% block $variables[block] %}$markdown{% endblock %}";
        }

        return $this->twig->render($template, $variables);
    }

    /**
     * Parses LESS files
     *
     * @param object $resource
     * @return string
     */
    protected function parseLess(Resource $resource)
    {
        if ($this->config['compile_less']) {
            $this->outputter->write('<comment>Compiling less: </comment>' . $resource->getBasename());
            return $this->less->compile($resource->getContents());
        }

        $this->outputter->write('<comment>Skipping compilation on </comment>' . $resource->getBasename());
        return $resource->getContents();
    }

    /**
     * Parses a string that looks like a twig template
     *
     * @param object $resource
     * @param array $categories
     * @return string
     */
    protected function parseTwigFromString(Resource $resource, array $categories = array())
    {
        $this->outputter->write('<comment>Parsing Twig String: </comment>' . $resource->getBasename());
        return $this->twig->render($resource->getContents(), $this->getFileVariables($resource, $categories));
    }

    /**
     * Parses a twig template
     *
     * @param string $tpl
     * @param array $vars
     * @return string
     */
    public function parseTwigFromFile($tpl, array $vars = array())
    {
        $this->outputter->write('<comment>Parsing Twig Template: </comment>' . $tpl);
        $tpl = $this->twig->loadTemplate($tpl);
        return $tpl->render($vars);
    }
}

?>
