<?php
namespace Brainwave\Translator;

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

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;
use \Brainwave\Translator\TranslatorManager;

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
    public function register(Container $app)
    {
        $app['translator'] = function ($app) {
            $translator = new TranslatorManager();
            $translator->setLocale($app['settings']->get('app.locale', 'en'));
            return $translator;
        };
    }
}
