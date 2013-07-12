<?php
/**
 * Container.php
 * A very simple IoC Container with the help of Pimple.
 *
 * @package Bolido
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

class Container extends \Pimple
{
    /**
     * Construct, does the wiring for every class.
     *
     * @param object $config
     * @param object $benchmark
     * @return void
     */
    public function __construct(\Bolido\Adapters\BaseConfig $config, \Bolido\Benchmark $benchmark)
    {
        /**
         * Inject The Config and Benchmark Objects
         */
        $this['config'] = $config;
        $this['benchmark'] = $benchmark;

        /**
         * APC and FileCache Objects
         */
        $this['apc_cache'] = function ($c) {
            return new \Bolido\Cache\ApcEngine($c['config']->mainUrl);
        };

        $this['file_cache'] = function ($c) {
            return new \Bolido\Cache\FileEngine($c['config']->cacheDir);
        };

        /**
         * Shared Main Cache Object
         */
        $this['cache'] = $this->share(function ($c) {
            if ($this['config']->cacheMode == 'apc' && function_exists('apc_store'))
                return $this['apc_cache'];

            return $this['file_cache'];
        });

        /**
         * Shared Hooks Object
         */
        $this['hooks'] = $this->share(function ($c){
            $files = $c['cache']->read('hook_files');
            if (empty($files))
            {
                $files = glob($c['config']->moduleDir . '/*/hooks/*.php');
                if (!empty($this['hook_files']))
                    $c['cache']->store('hook_files', $files, (15*60));
            }

            return new \Bolido\Hooks($files);
        });

        /**
         * Database Object
         */
        $this['db'] = function() {
            return new \Bolido\Database();
        };

        /**
         * Shared ErrorHandler Object
         */
        $this['error'] = $this->share(function($c){
            return new \Bolido\ErrorHandler($c);
        });

        /**
         * Shared Router Object
         */
        $this['router'] = $this->share(function ($c){
            return $c['hooks']->run('modify_router', new \Bolido\Router($_SERVER['REQUEST_METHOD']));
        });

        /**
         * Shared User Object
         * @throw InvalidArgumentException when an invalid Object was defined
         */
        $this['user'] = $this->share(function ($c){
            $reflection = new \ReflectionClass($c['config']->usersModule);
            if ($reflection->implementsInterface('\Bolido\Interfaces\IUser'))
                return $reflection->newInstance($c);

            throw new \InvalidArgumentException('Invalid Users Module ' . $c['config']->usersModule);
        });

        /**
         * Shared UrlParser Object
         */
        $this['urlparser'] = $this->share(function ($c) {
            return new \Bolido\UrlParser($_SERVER['REQUEST_URI'], $c['config']);
        });

        /**
         * Shared Session Object
         */
        $this['session'] = $this->share(function ($c) {
            return new \Bolido\Session($c['config']->mainUrl);
        });

        /**
         * Shared Language Object
         */
        $this['lang'] = $this->share(function ($c){
            return $c['hooks']->run('modify_lang', new \Bolido\Lang($c['config']));
        });

        /**
         * Twig Locator Engine
         */
        $this['twig_locator'] = function ($c) {
            return new \Bolido\Twig\Locator($c['config']);
        };

        /**
         * Twig Bolido Extension
         */
        $this['twig_extension'] = function ($c) {
            return new \Bolido\Twig\Extension($c);
        };

        /**
         * Shared Twig template Object
         */
        $this['twig'] = $this->share(function ($c) {
            $twig = new \Twig_Environment($c['twig_locator'], $c['config']->twigOptions);
            $twig->addExtension($c['twig_extension']);
            return $twig;
        });
    }

}
?>
