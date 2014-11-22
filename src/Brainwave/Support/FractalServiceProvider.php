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
use \League\Fractal\Manager;
use \Pimple\ServiceProviderInterface;
use \League\Fractal\Serializer\ArraySerializer;
use \League\Fractal\Serializer\JsonApiSerializer;
use \League\Fractal\Serializer\DataArraySerializer;

/**
 * FractalServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.3-dev
 *
 */
class FractalServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $this->app = $app;

        $this->registerFractal();
        $this->registerJsonApiFractal();
        $this->registerArrayFractal();
        $this->registerDataArrayFractal();
    }

    protected function registerFractal()
    {
        $this->app['fractal'] = function ($app) {
            $manager = new Manager();
            return $manager;
        };
    }

    protected function registerJsonApiFractal()
    {
        $this->app['fractal.json'] = function ($app) {
            $manager = new Manager();
            $manager->setSerializer(new JsonApiSerializer());
            return $manager;
        };
    }

    protected function registerArrayFractal()
    {
        $this->app['fractal.array'] = function ($app) {
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            return $manager;
        };
    }

    protected function registerDataArrayFractal()
    {
        $this->app['fractal.data.array'] = function ($app) {
            $manager = new Manager();
            $manager->setSerializer(new DataArraySerializer());
            return $manager;
        };
    }
}
