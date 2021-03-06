<?php
namespace Brainwave\Cookie\Interfaces;

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

use \Brainwave\Http\Interfaces\HeadersInterface;
use \Brainwave\Collection\Interfaces\CollectionInterface;

/**
 * CookiesInterface
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.1-dev
 *
 */
interface CookiesJarInterface extends CollectionInterface
{
    /**
     * @return void
     */
    public function setHeaders(HeadersInterface $headers);

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function setHeader(HeadersInterface $headers, $name, $value);

    /**
     * @param string $name
     *
     * @return void
     */
    public function deleteHeader(HeadersInterface $headers, $name, $value = []);

    public function parseHeader($header);
}
