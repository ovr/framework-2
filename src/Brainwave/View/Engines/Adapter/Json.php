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

use \Pimple\Container;
use \Brainwave\View\Engines\Interfaces\EngineInterface as EnginesContract;

/**
 * Json
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Json implements EnginesContract
{
    /**
     * Set Path
     *
     * @var string
     */
    protected $status;

    /**
     * App
     *
     * @var \Brainwave\Workbanch\Workbanch
     */
    protected $container;

    /**
     * Json data
     *
     * @var array
     */
    protected $factory;

    /**
     * Construct
     *
     * @param \Pimple\Container                                  $container
     * @param \Brainwave\View\Engines\Interfaces\EngineInterface $factory
     */
    public function __construct(Container $container, ViewFactory $factory)
    {
        $this->container = $container;
        $this->factory   = $factory;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string $path
     * @param  array  $data
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
        if ($data['options'] === $this->container['settings']['http::json.option']) {
            $options = $this->container['settings']['http::json.option'];
        } else {
            $options = $data['options'];
        }

        return $this->evaluateStatus(
            $path,
            $data,
            $options
        );
    }

    /**
     * Get the evaluated contents of the view at the given status.
     *
     * @param  integer $status
     * @param  array   $data
     *
     * @return string
     */
    protected function evaluateStatus($status = 200, array $data = [], $option = 0)
    {
        $container= $this->container;
        $factory = $this->factory;

        //append error bool
        if (!$factory->has('error')) {
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
            [
                'method' => $container['request']->getMethod(),
                'name' => $container['request']->get('name'),
                'headers' => $container['request']->getHeaders(),
                'params' => $container['request']->params()
            ]
        );

        $container['response']->setStatus($status);
        $container['response']->addHeaders(
            array_merge(
                ['Content-Type', 'application/json'],
                $data['json.headers']
            )
        );

        $jsonp_callback = $container->request->get('callback', null);

        if ($jsonp_callback !== null) {
            $container['response']->write($jsonp_callback.'('.json_encode($data, $option).')');
        } else {
            $container['response']->write(json_encode($data, $option));
        }

        $container->stop();
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception $e
     *
     * @return void
     *
     * @throws $e
     */
    protected function handleViewException($e)
    {
        ob_get_clean();
        throw $e;
    }
}
