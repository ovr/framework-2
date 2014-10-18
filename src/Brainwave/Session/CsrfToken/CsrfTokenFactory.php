<?php
namespace Brainwave\Session\CsrfToken;

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

use \Brainwave\Crypt\Crypt;
use \Brainwave\Session\Interfaces\SegmentHandlerInterface;

/**
 * CsrfTokenFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class CsrfTokenFactory
{
    /**
     * A cryptographically-secure random value generator.
     *
     * @var RandvalInterface
     */
    protected $randval;

    /**
     * Session segment for values in this class.
     *
     * @var Segment
     */
    protected $segment;

    /**
     * Constructor.
     *
     * @param SegmentHandlerInterface $segment A segment for values in this class.
     * @param Crypt $randval A cryptographically-secure random
     * value generator.
     */
    public function __construct(SegmentHandlerInterface $segment, Crypt $randval)
    {
        $this->segment = $segment;
        $this->randval = $randval;
        if (! isset($this->segment->value)) {
            $this->regenerateValue();
        }
    }

    /**
     * Checks whether an incoming CSRF token value is valid.
     *
     * @param string $value The incoming token value.
     * @return bool True if valid, false if not.
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
        return $this->segment->value;
    }

    /**
     * Regenerates the value of the outgoing CSRF token.
     *
     * @return void
     */
    public function regenerateValue()
    {
        $this->segment->value = $this->randval->rand()->str('128');
    }
}
