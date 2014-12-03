<?php
namespace Brainwave\Session\Handler;

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

use Brainwave\Contracts\Http\Request as RequestContract;
use Brainwave\Contracts\Cookie\Factory as CookieContract;

/**
 * Session
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
class CookieSessionHandler implements \SessionHandlerInterface
{
    /**
     * The cookie jar instance.
     *
     * @var CookieContract
     */
    protected $cookie;

    /**
     * The request instance.
     *
     * @var RequestContract
     */
    protected $request;

    /**
     * Create a new cookie driven handler instance.
     *
     * @param CookieContract $cookie
     * @param int            $minutes
     *
     * @return void
     */
    public function __construct(CookieContract $cookie, $minutes)
    {
        $this->cookie = $cookie;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        return $this->request->getCookie($sessionId) ?: '';
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        //TODO
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $this->cookie->remove($sessionId);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Set the request instance.
     *
     * @param RequestContract $request
     *
     * @return void
     */
    public function setRequest(RequestContract $request)
    {
        $this->request = $request;
    }
}
