<?php
namespace Brainwave\Session\Token;

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

use \RandomLib\Factory as RandomLib;
use \Brainwave\Contracts\Session\Factory as Session;

/**
 * CsrfTokenFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Csrf
{
    /**
     * Generator instance.
     *
     * @var RandomLib
     */
    protected $rand;

    /**
     * Session segment for values in this class.
     *
     * @var Session
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param RandomLib $rand
     * @param Session   $session A session for values in this class.
     */
    public function __construct(RandomLib $rand, Session $session)
    {
        $this->rand    = $rand;
        $this->session = $session;

        if (!$this->session->value) {
            $this->regenerateValue();
        }
    }

    /**
     * Checks whether an incoming CSRF token value is valid.
     *
     * @param string $value The incoming token value.
     *
     * @return bool
     */
    public function isValid($value)
    {
        return $value === $this->getValue();
    }

    /**
     * Gets the value of the outgoing CSRF token.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->session->value;
    }

    /**
     * Regenerates the value of the outgoing CSRF token.
     *
     * @return void
     */
    public function regenerateValue()
    {
        $this->session->value = $this->rand->generate(128);
    }

    /**
     * Regenerates and replaces the current session id; also regenerates the
     * CSRF token value if one exists.
     *
     * @return bool
     */
    public function regenerateId()
    {
        $result = $this->call('session_regenerate_id', [true]);

        if ($result) {
            $this->regenerateValue();
        }

        return $result;
    }
}
