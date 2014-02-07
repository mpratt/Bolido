<?php
/**
 * MarkdownParser.php
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
use Ciconia\Ciconia;
use Ciconia\Extension\Gfm;
use Bolido\Filesystem\Resource;

/**
 * Parser of Markdown files
 */
class MarkdownParser implements ParserInterface
{
    /** @var object Markdown Parser */
    protected $parser;

    /** @var object Implementing ParserInterface */
    protected $twig;

    /**
     * Construct
     *
     * @param object $config
     * @param object $twig
     * @return void
     */
    public function __construct(Config $config, ParserInterface $twig)
    {
        $this->twig = $twig;
        $this->parser = new Ciconia();
        if ($config['extended_markdown']) {
            $this->parser->addExtension(new Gfm\FencedCodeBlockExtension());
            $this->parser->addExtension(new Gfm\TaskListExtension());
            $this->parser->addExtension(new Gfm\InlineStyleExtension());
            $this->parser->addExtension(new Gfm\WhiteSpaceExtension());
            $this->parser->addExtension(new Gfm\TableExtension());
            $this->parser->addExtension(new Gfm\UrlAutoLinkExtension());
        }
    }

    /** inline {@inheritdoc} */
    public function parseResource(Resource $resource, array $variables = array())
    {
        return $this->parseString($resource->getContents(), $variables);
    }

    /** inline {@inheritdoc} */
    public function parseString($string, array $variables = array())
    {
        $markdown = $this->parser->render($string);
        if (strtolower($variables['layout']) == 'raw' || preg_match('~\s*{%\s*extend~Asi', $markdown)) {
            $template = $markdown;
        } else {
            $template = "{% extends \"$variables[layout]\" %}{% block $variables[block] %}$markdown{% endblock %}";
        }

        return $this->twig->parseString($markdown, $variables);
    }
}

?>
