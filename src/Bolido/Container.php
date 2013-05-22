<?php
/**
 * Container.php
 * A very simple IoC Container with the help of Pimple.
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

class Container extends \Pimple
{
    /**
     * Construct, does the wiring for every
     * class.
     *
     * @param object $config
     * @param object $benchmark
     * @return void
     */
    public function __construct(\Bolido\Adapters\BaseConfig $config, \Bolido\Benchmark $benchmark)
    {
        $this['config'] = $config;
        $this['benchmark'] = $benchmark;

        $this['apc_cache'] = function ($c) { return new \Bolido\Cache\ApcEngine($c['config']->mainUrl); };
        $this['file_cache'] = function ($c) { return new \Bolido\Cache\FileEngine($c['config']->cacheDir); };

        $this['urlparser'] = $this->share(function ($c) {
            return new \Bolido\UrlParser($_SERVER['REQUEST_URI'], $c['config']);
        });

        $this['session'] = $this->share(function ($c) {
            return new \Bolido\Session($c['config']->mainUrl);
        });

        $this['lang'] = $this->share(function ($c){
            return $c['hooks']->run('modify_lang', new \Bolido\Lang($c['config']));
        });

        $this['cache'] = $this->share(function ($c) {
            if ($this['config']->cacheMode == 'apc' && function_exists('apc_store'))
                return $this['apc_cache'];

            return $this['file_cache'];
        });

        $this['hooks'] = $this->share(function ($c){
            // Load hooks/plugins from all the modules
            $hookFiles = $c['cache']->read('hook_files');
            if (empty($hookFiles))
            {
                $hookFiles = glob($c['config']->moduleDir . '/*/hooks/*.php');
                if (!empty($hookFiles))
                    $c['cache']->store('hook_files', $hookFiles, (15*60));
            }

            return new \Bolido\Hooks($hookFiles);
        });

        $this['template'] = $this->share(function ($c){
            return $c['hooks']->run('extend_template', new \Bolido\Template($c['config'], $c['lang'], $c['session'], $c['hooks']));
        });

        $this['error'] = $this->share(function($c){
            return new \Bolido\ErrorHandler($c['hooks'], $c['template']);
        });

        $this['router'] = $this->share(function ($c){
            return $c['hooks']->run('modify_router', new \Bolido\Router($_SERVER['REQUEST_METHOD']));
        });

        $this['db'] = $this->share(function($c) {
            return new \Bolido\Database($c['config']->dbInfo);
        });

        $this['user'] = $this->share(function ($c){
            $reflection = new \ReflectionClass($c['config']->usersModule);
            if ($reflection->implementsInterface('\Bolido\Interfaces\IUser'))
                return $reflection->newInstanceArgs(array($c['config'], $c['db'], $c['session'], $c['hooks']));

            return null;
        });
    }

}
?>
