<?php
namespace Brainwave\Contracts\Routing;

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

/**
 * Route
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Route
{
    /**
     * @return void
     */
    public static function setDefaultConditions(array $defaultConditions);

    public static function getDefaultConditions();

    /**
     * @return string
     */
    public function getPattern();

    /**
     * @param string $pattern
     *
     * @return \Brainwave\Routing\Route
     */
    public function setPattern($pattern);

    public function getCallable();

    /**
     * @return void
     */
    public function setCallable(callable $callable);

    public function getConditions();

    /**
     * @return \Brainwave\Routing\Route
     */
    public function setConditions(array $conditions);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return \Brainwave\Routing\Route
     */
    public function setName($name);

    public function getParams();

    /**
     * @return \Brainwave\Routing\Route
     */
    public function setParams(array $params);

    /**
     * @return string
     */
    public function getParam($index);

    /**
     * @return \Brainwave\Routing\Route
     */
    public function setParam($index, $value);

    /**
     * @return \Brainwave\Routing\Route
     */
    public function setHttpMethods();

    public function getHttpMethods();

    /**
     * @return void
     */
    public function appendHttpMethods();

    /**
     * @return \Brainwave\Routing\Route
     */
    public function via();

    /**
     * @return boolean
     */
    public function supportsHttpMethod($method);

    public function getMiddleware();

    /**
     * @return \Brainwave\Routing\Route
     */
    public function setMiddleware($middleware);

    /**
     * @return boolean
     */
    public function matches($resourceUri);

    /**
     * @return \Brainwave\Routing\Route
     */
    public function name($name);

    /**
     * @return \Brainwave\Routing\Route
     */
    public function conditions(array $conditions);

    /**
     * @return boolean
     */
    public function dispatch();
}
