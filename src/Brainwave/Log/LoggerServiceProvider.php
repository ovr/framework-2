<?php
namespace Brainwave\Log;

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

use \Monolog\Logger;
use \Pimple\Container;
use \Monolog\Handler\StreamHandler;
use \Pimple\ServiceProviderInterface;

/**
 * LoggerServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class LoggerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['logger'] = function () use ($app) {
            return $app['monolog'];
        };

        $app['monolog.logger.class'] = 'Monolog\Logger';

        $app['monolog'] = $app->share(function ($app) {
            $log = new $app['monolog.logger.class']($app['monolog.name']);

            $log->pushHandler($app['monolog.handler']);

            if ($app['debug'] && isset($app['monolog.handler.debug'])) {
                $log->pushHandler($app['monolog.handler.debug']);
            }

            return $log;
        });

        $app['monolog.handler'] = function () use ($app) {
            return new StreamHandler($app['monolog.logfile'], $app['monolog.level']);
        };

        $app['monolog.level'] = function () {
            return Logger::DEBUG;
        };

        $app['monolog.name'] = 'myapp';
    }

    public function boot(Container $app)
    {
        $app->before(function (Request $request) use ($app) {
            $app['monolog']->addInfo('> '.$request->getMethod().' '.$request->getRequestUri());
        });

        /*
         * Priority -4 is used to come after those from SecurityServiceProvider (0)
         * but before the error handlers added with Silex\Application::error (defaults to -8)
         */
        $app->error(function (\Exception $e) use ($app) {
            $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
            if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
                $app['monolog']->addError($message, array('exception' => $e));
            } else {
                $app['monolog']->addCritical($message, array('exception' => $e));
            }
        }, -4);

        $app->after(function (Request $request, Response $response) use ($app) {
            if ($response instanceof RedirectResponse) {
                $app['monolog']->addInfo('< '.$response->getStatusCode().' '.$response->getTargetUrl());
            } else {
                $app['monolog']->addInfo('< '.$response->getStatusCode());
            }
        });
    }
}
