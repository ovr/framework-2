<?php
namespace Brainwave\View\Engines;

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

use Brainwave\Contracts\View\Compiler as CompilerContract;

/**
 * CompilerEngine
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class CompilerEngine extends PhpEngine
{
    /**
     * Compiler instance.
     *
     * @var CompilerContract
     */
    protected $compiler;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    /**
     * Create a view engine instance.
     *
     * @param CompilerContract $compiler
     *
     * @return void
     */
    public function __construct(CompilerContract $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;

        // If this given view has expired, which means it has simply been edited since
        // it was last compiled, we will re-compile the views so we can evaluate a
        // fresh copy of the view. We'll pass the compiler the path of the view.
        if ($this->compiler->isExpired($path)) {
            $this->compiler->compile($path);
        }

        $compiled = $this->compiler->getCompiledPath($path);

        // Once we have the path to the compiled file, we will evaluate the paths with
        // typical PHP just like any other templates. We also keep a stack of views
        // which have been rendered for right exception messages to be generated.
        $results = $this->evaluatePath($compiled, $data);

        array_pop($this->lastCompiled);

        return $results;
    }

    /**
     * Handle a view exception.
     *
     * @param \Exception $e
     *
     * @return void
     *
     * @throws $e
     */
    protected function handleViewException($e)
    {
        $e = new \ErrorException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);
        ob_get_clean();
        throw $e;
    }

    /**
     * Get the exception message for an exception.
     *
     * @param \Exception $e
     *
     * @return string
     */
    protected function getMessage($e)
    {
        return $e->getMessage().' (View: '.realpath(last($this->lastCompiled)).')';
    }

    /**
     * Get the compiler implementation.
     *
     * @return CompilerContract
     */
    public function getCompiler()
    {
        return $this->compiler;
    }
}
