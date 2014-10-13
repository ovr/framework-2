<?php
namespace Brainwave\Routing\Interfaces;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.3-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

/**
 * RouteInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
interface RouteInterface
{
    public static function setDefaultConditions(array $defaultConditions);

    public static function getDefaultConditions();

    public function getPattern();

    public function setPattern($pattern);

    public function getCallable();

    public function setCallable($callable);

    public function getConditions();

    public function setConditions(array $conditions);

    public function getName();

    public function setName($name);

    public function getParams();

    public function setParams(array $params);

    public function getParam($index);

    public function setParam($index, $value);

    public function setHttpMethods();

    public function getHttpMethods();

    public function appendHttpMethods();

    public function via();

    public function supportsHttpMethod($method);

    public function getMiddleware();

    public function setMiddleware($middleware);

    public function matches($resourceUri);

    public function name($name);

    public function conditions(array $conditions);

    public function dispatch();
}
