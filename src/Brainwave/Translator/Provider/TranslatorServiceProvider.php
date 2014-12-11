<?php
namespace Brainwave\Translator\Provider;

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

use Brainwave\Filesystem\FileLoader;
use Brainwave\Translator\Manager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * TranslatorServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class TranslatorServiceProvider implements ServiceProviderInterface
{
    /**
     * Register translator
     */
    public function register(Container $container)
    {
        $container['translator.path'] = '';

        $container['translator'] = function ($container) {
            $translator = new Manager();
            $translator->setLocale($container['settings']->get('app::locale', 'en'));
            $translator->setLoader(
                new FileLoader(
                    $container['files'],
                    $container['translator.path']
                )
            );

            return $translator;
        };
    }
}
