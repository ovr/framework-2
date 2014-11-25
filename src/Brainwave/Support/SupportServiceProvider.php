<?php
namespace Brainwave\Support;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.4-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Pimple\Container;
use \Brainwave\Support\Arr;
use \Brainwave\Support\Str;
use \Brainwave\Support\Helpers;
use \Pimple\ServiceProviderInterface;

/**
 * SupportServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.2-dev
 *
 */
class SupportServiceProvider implements ServiceProviderInterface
{
    protected $container;

    public function register(Container $container)
    {
        $this->container= $container;

        $this->registerHelpers();
        $this->registerArr();
        $this->registerStr();
    }

    /**
     * Register Helpers
     *
     * @return  \Brainwave\Support\Helpers
     */
    protected function registerHelpers()
    {
        $this->app['helpers'] = function ($container) {
            return new Helpers();
        };
    }

    /**
     * Register Arr
     *
     * @return  \Brainwave\Support\Arr
     */
    protected function registerArr()
    {
        $this->app['arr'] = function ($container) {
            return new Arr();
        };
    }

    /**
     * Register Str
     *
     * @return  \Brainwave\Support\Str
     */
    protected function registerStr()
    {
        $this->app['str'] = function ($container) {
            return new Str();
        };
    }
}
