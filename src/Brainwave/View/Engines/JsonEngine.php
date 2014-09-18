<?php
namespace Brainwave\View\Engines;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.2-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\View\Engines\Interfaces\EnginesInterface;

/**
 * JsonEngine
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class JsonEngine implements EnginesInterface
{
    /**
     * Set Path
     * @var string
     */
    protected $status;

    /**
     * App
     * @var \Brainwave\Workbanch\Workbanch
     */
    protected $app;

    /**
     * Json data
     * @var array
     */
    protected $collection;

    /**
     *
     * @param void $app        \Brainwave\Workbench\Workbench
     * @param array $collection
     */
    public function __construct(Workbench $app, $collection)
    {
        $this->app = $app;
        $this->collection = $collection;
    }

    /**
     * Get the evaluated contents of the view.
     * @param  array   $data
     * @return string
     */
    public function get(array $data = array())
    {
        if ($data['options'] === $this->app['settings']['json.option']) {
            $options = $this->app['settings']['json.option'];
        } else {
            $options = $data['options'];
        }
        
        return $this->evaluateStatus(
            $this->status,
            $data,
            $options
        );
    }

    /**
     * Set path
     * @param string $path
     * @return $this \Brainwave\View\Engines
     */
    public function set($path)
    {
        $this->status = $path;
        return $this;
    }

    /**
     * Get the evaluated contents of the view at the given status.
     * @param  string  $status
     * @param  array   $data
     * @return string
     */
    protected function evaluateStatus($status = 200, array $data = array(), $option = 0)
    {
        $app = $this->app;
        $collection = $this->collection;

        //append error bool
        if (!$collection->has('error')) {
            $data['error'] = false;
        } elseif ($status == 404 || $status == 500) {
            $data['error'] = true;
        }

        if ($status == 404) {
            $data['status'] = 404;
            $data['msg'] = 'Invalid route';
        } elseif ($status == 500) {
            $data['status'] = 500;
            $data['msg'] ='Empty response';
        } else {
            //append status code
            $data['status'] = $status;
        }

        //add flash messages
        if (isset($this->data->flash) && is_object($this->data->flash)) {
            $flash = $this->data->flash->getMessages();
            if (count($flash)) {
                $data['flash'] = $flash;
            } else {
                unset($data['flash']);
            }
        }

        $data = array_merge(
            $data,
            array(
                'method' => $app['request']->getMethod(),
                'name' => $app['request']->get('name'),
                'headers' => $app['request']->getHeaders(),
                'params' => $app['request']->params()
            )
        );

        $app['response']->setStatus($status);
        $app['response']->addHeaders(
            array_merge(
                array('Content-Type', 'application/json'),
                $data['j.headers']
            )
        );

        $jsonp_callback = $app->request->get('callback', null);

        if ($jsonp_callback !== null) {
            $app['response']->write($jsonp_callback.'('.json_encode($data, $option).')');
        } else {
            $app['response']->write(json_encode($data, $option));
        }

        $app->stop();
    }

    /**
     * Handle a view exception.
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
