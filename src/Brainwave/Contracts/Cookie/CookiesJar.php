<?php
namespace Brainwave\Contracts\Cookie;

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

use \Brainwave\Contracts\Collection;
use \Brainwave\Contracts\Http\Headers;

/**
 * CookiesJar
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface CookiesJar extends Collection
{
    /**
     * @return void
     */
    public function setHeaders(Headers $headers);

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function setHeader(Headers $headers, $name, $value);

    /**
     * @param string $name
     *
     * @return void
     */
    public function deleteHeader(Headers $headers, $name, $value = []);

    public function parseHeader($header);
}
