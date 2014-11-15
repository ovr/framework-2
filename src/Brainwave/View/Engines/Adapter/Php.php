<?php
namespace Brainwave\View\Engines\Adapter;

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

use Brainwave\Contracts\View\Engines as EnginesContract;

/**
 * Php
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Php implements EnginesContract
{
    /**
     * Set Path
     * @var string
     */
    protected $path;

    /**
     * Get the evaluated contents of the view.
     *
     * @param  array   $data
     * @return string
     */
    public function get(array $data = [])
    {
        return $this->evaluatePath($this->path, $data);
    }

    /**
     * Set path
     *
     * @param string $path
     * @return $this \Brainwave\View\Engines
     */
    public function set($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    protected function evaluatePath($path, array $data)
    {
        if (!is_file($path)) {
            throw new \RuntimeException(
                "Cannot render template `$path` because the template does not exist.
                Make sure your view's template directory is correct."
            );
        }

        extract($data, EXTR_PREFIX_SAME, "brain");

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();

        try {
            require_once $path;
            // Return temporary output buffer content, destroy output buffer
            return ltrim(ob_get_clean());
        } catch (\Exception $e) {
            // Return temporary output buffer content, destroy output buffer
            $this->handleViewException($e);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception  $e
     * @return void
     * @throws $e
     */
    protected function handleViewException($e)
    {
        ob_get_clean();
        throw $e;
    }
}
