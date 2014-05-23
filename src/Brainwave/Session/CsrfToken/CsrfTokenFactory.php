<?php namespace Brainwave\Session\CsrfToken;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Crypt\Crypt;
use \Brainwave\Session\Interfaces\SegmentFactoryInterface;

/**
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
     * @param Segment $segment A segment for values in this class.
     * @param RandvalInterface $randval A cryptographically-secure random
     * value generator.
     */
    public function __construct(SegmentFactoryInterface $segment, Crypt $randval)
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
