<?php
namespace Brainwave\Support\Autoloader;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Support\Autoloader\ClassLoader;
use \Brainwave\Support\Services\Interfaces\ServiceProviderInterface;

/**
 * TranslatorServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class AutoloaderServiceProvider implements ServiceProviderInterface
{

    /**
     * {@inheritdoc}
     */
    public function register(Workbench $app)
    {
        $app['autoloader'] = function ($app) {
            $classLoader = new ClassLoader();
            return $classLoader;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Workbench $app)
    {

    }
}
