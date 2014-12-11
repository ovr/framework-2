<?php
namespace Brainwave\Contracts\Http;

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
 * Response
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface Response
{
    /**
     * Send HTTP headers and body
     *
     * @return \Brainwave\Contracts\Http\Response
     */
    public function send();

    /**
     * Set the content on the response.
     *
     * @param  mixed $content
     *
     * @return $this
     */
    public function setContent($content);
}
