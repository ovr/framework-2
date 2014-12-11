<?php
namespace Brainwave\Support\Provider;

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
 */

use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\JsonApiSerializer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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
    public function register(Container $container)
    {
        $this->registerFractal($container);
        $this->registerJsonApiFractal($container);
        $this->registerArrayFractal($container);
        $this->registerDataArrayFractal($container);
    }

    protected function registerFractal(Container $container)
    {
        $container['fractal'] = function () {
            $manager = new Manager();

            return $manager;
        };
    }

    protected function registerJsonApiFractal(Container $container)
    {
        $container['fractal.json'] = function () {
            $manager = new Manager();
            $manager->setSerializer(new JsonApiSerializer());

            return $manager;
        };
    }

    protected function registerArrayFractal(Container $container)
    {
        $container['fractal.array'] = function () {
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());

            return $manager;
        };
    }

    protected function registerDataArrayFractal(Container $container)
    {
        $container['fractal.data.array'] = function () {
            $manager = new Manager();
            $manager->setSerializer(new DataArraySerializer());

            return $manager;
        };
    }
}
