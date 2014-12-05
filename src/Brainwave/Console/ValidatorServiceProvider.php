<?php
namespace Brainwave\Console;

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

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\DefaultTranslator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator;

/**
 * ValidatorServiceProvider
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class ValidatorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['validator'] = function () use ($app) {
            return new Validator\RecursiveValidator(
                $app['validator.mapping.class_metadata_factory'],
                $app['validator.validator_factory'],
                $app['validator.default_translator']
            );
        };

        $app['validator.mapping.class_metadata_factory'] = function () use ($app) {
            return new LazyLoadingMetadataFactory(new StaticMethodLoader());
        };

        $app['validator.validator_factory'] = function () {
            return new ConstraintValidatorFactory();
        };

        $app['validator.default_translator'] = function () {
            if (!class_exists('Symfony\\Component\\Validator\\DefaultTranslator')) {
                return array();
            }

            return new DefaultTranslator();
        };

        if (isset($app['validator.class_path'])) {
            $app['autoloader']->registerNamespace('Symfony\\Component\\Validator', $app['validator.class_path']);
        }
    }
}
