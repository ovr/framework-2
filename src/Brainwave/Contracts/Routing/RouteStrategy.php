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
 * CustomStrategy
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface RouteStrategyInterface
{
    /**
     * Types of route strategies
     */
    const REQUEST_RESPONSE_STRATEGY = 0;
    const RESTFUL_STRATEGY          = 1;
    const URI_STRATEGY              = 2;

    /**
     * Tells the implementor which strategy to use, this should override any higher
     * level setting of strategies, such as on specific routes
     *
     * @param  integer $strategy
     *
     * @return void
     */
    public function setStrategy($strategy);

    /**
     * Gets global strategy
     *
     * @return integer
     */
    public function getStrategy();
}
