<?php
namespace Brainwave\Contracts\Application;

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

use \Brainwave\Contracts\Support\Collection;

/**
 * Environment
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Environment extends Collection
{
    /**
     * @return void
     */
    public function parse(array $environment);

    /**
     * @return void
     */
    public function mock(array $settings = []);

    /**
     * Detect the application's current environment.
     *
     * @param  array|string $environments
     * @param  array|null   $consoleArgs
     *
     * @return bool
     */
    public function detect($environments, $consoleArgs = null);
}
