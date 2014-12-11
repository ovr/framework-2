<?php
namespace Brainwave\Contracts\View;

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

/**
 * View
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface View
{
    /**
     * @param string|null $template
     *
     * @return void
     */
    public function make($engine, $template, array $data = []);

    /**
     * @param null $template
     *
     * @return string
     */
    public function fetch($engine, $template, array $data = []);
}
