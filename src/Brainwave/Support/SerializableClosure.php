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
 */

use Jeremeamia\SuperClosure\SerializableClosure as SuperClosure;

/**
 * SerializableClosure
 *
 * Extends SuperClosure for backwards compatibility.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class SerializableClosure extends SuperClosure
{
    /**
     * The code for the closure
     *
     * @var string
     */
    protected $code;

    /**
     * The variables that were "used" or imported from the parent scope
     *
     * @var array
     */
    protected $variables;

    /**
     * Returns the code of the closure being serialized
     *
     * @return string
     */
    public function getCode()
    {
        $this->determineCodeAndVariables();

        return $this->code;
    }

    /**
     * Returns the "used" variables of the closure being serialized
     *
     * @return array
     */
    public function getVariables()
    {
        $this->determineCodeAndVariables();

        return $this->variables;
    }

    /**
     * Uses the serialize method directly to lazily fetch the code and variables if needed
     */
    protected function determineCodeAndVariables()
    {
        if (!$this->code) {
            list($this->code, $this->variables) = unserialize($this->serialize());
        }
    }
}
