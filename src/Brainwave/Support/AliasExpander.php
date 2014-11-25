<?php
namespace Brainwave\Support;

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
 * Str
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class AliasExpander
{
    /**
     * Expands class alias in a context where this method is called.
     *
     * @param  string            class alias
     * @param  int               how deep is wrapped this method call
     *
     * @return string            fully qualified class name
     *
     * @throws \RuntimeException when origin of call cannot be found in backtrace
     * @throws \LogicException   when empty alias name passed
     */
    public function expand($facade)
    {

    }

    /**
     * Expands class alias in a file:line context.
     *
     * @param  string          class alias
     * @param  string          file path
     * @param  int             line number
     *
     * @return string          fully qualified class name
     *
     * @throws \LogicException when empty class alias name passed
     */
    public function expandExplicit($name, $file, $line = 0)
    {

    }
}
