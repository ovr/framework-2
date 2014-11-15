<?php
namespace Brainwave\Contracts\Routing;

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

/**
 * ResponseFactory
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.9.4-dev
 *
 */
interface ResponseFactory
{
    /**
     * Return a new response from the application.
     *
     * @param  string $content
     * @param  int    $status
     * @param  array  $headers
     *
     * @return \Brainwave\Http\Response
     */
    public function make($content = '', $status = 200, array $headers = array());

    /**
     * Return a new view response from the application.
     *
     * @param  string $view
     * @param  array  $data
     * @param  int    $status
     * @param  array  $headers
     *
     * @return \Brainwave\Http\Response
     */
    public function view($view, $data = array(), $status = 200, array $headers = array());

    /**
     * Return a new JSON response from the application.
     *
     * @param  string|array  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     *
     * @return \Brainwave\Http\Response
     */
    public function json($data = array(), $status = 200, array $headers = array(), $options = 0);

    /**
     * Return a new JSONP response from the application.
     *
     * @param  string  $callback
     * @param  string|array $data
     * @param  int          $status
     * @param  array        $headers
     * @param  int          $options
     *
     * @return \Brainwave\Http\Response
     */
    public function jsonp($callback, $data = array(), $status = 200, array $headers = array(), $options = 0);

    /**
     * Return a new streamed response from the application.
     *
     * @param  \Closure $callback
     * @param  int      $status
     * @param  array    $headers
     *
     * @return \GuzzleHttp\Stream\StreamInterface
     */
    public function stream($callback, $status = 200, array $headers = array());

    /**
     * Create a new file download response.
     *
     * @param  \SplFileInfo|string $file
     * @param  string              $name
     * @param  array               $headers
     * @param  null|string         $disposition
     *
     * @return \Brainwave\Http\BinaryFileResponse
     */
    public function download($file, $name = null, array $headers = array(), $disposition = 'attachment');

    /**
     * Create a new redirect response to the given path.
     *
     * @param  string  $path
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     *
     * @return \Brainwave\Http\Redirector
     */
    public function redirectTo($path, $status = 302, $headers = array(), $secure = null);

    /**
     * Create a new redirect response to a named route.
     *
     * @param  string  $route
     * @param  array   $parameters
     * @param  int     $status
     * @param  array   $headers
     *
     * @return \Brainwave\Http\Redirector
     */
    public function redirectToRoute($route, $parameters = array(), $status = 302, $headers = array());

    /**
     * Create a new redirect response to a controller action.
     *
     * @param  string  $action
     * @param  array   $parameters
     * @param  int     $status
     * @param  array   $headers
     *
     * @return \Brainwave\Http\Redirector
     */
    public function redirectToAction($action, $parameters = array(), $status = 302, $headers = array());
}
