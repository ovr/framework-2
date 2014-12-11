<?php
namespace Brainwave\Routing;

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

use Brainwave\Contracts\Routing\CustomStrategy;

/**
 * RouteStrategyTrait
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
trait RouteStrategyTrait
{
    /**
     * @var \Brainwave\Contracts\Routing\CustomStrategy|integer
     */
    protected $strategy;

    /**
     * Tells the implementor which strategy to use, this should override any higher
     * level setting of strategies, such as on specific routes
     *
     * @param integer|\Brainwave\Contracts\Routing\CustomStrategy $strategy
     *
     * @return void
     */
    public function setStrategy($strategy)
    {
        if (is_integer($strategy) || $strategy instanceof CustomStrategy) {
            $this->strategy = $strategy;

            return;
        }

        throw new \InvalidArgumentException(
            'Provided strategy must be an integer or an instance of [\Brainwave\Contracts\Routing\CustomStrategy]'
        );
    }

    /**
     * Gets global strategy
     *
     * @return integer
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
