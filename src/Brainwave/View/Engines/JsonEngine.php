<?php namespace Brainwave\View\Engines;

use \Brainwave\Workbench\Workbench;
use \Brainwave\Collection\Collection;
use \Brainwave\View\Engines\Interfaces\EnginesInterface;

class JsonEngine implements EnginesInterface
{
    /**
     * Set Path
     *
     * @var string
     */
    protected $status;

    protected $app;

    protected $collection;

    public function __construct($app, $collection)
    {
        $this->app = $app;
        $this->collection = $collection;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  array   $data
     * @return string
     */
    public function get(array $data = array())
    {
        return $this->evaluateStatus($this->status, $data, $this->app['settings']['json.option']);
    }

    /**
     * Set path
     *
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
     *
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

        $data = array_merge($data, array('method' => $app['request']->getMethod(), 'name' => $app['request']->get('name'), 'headers' => $app['request']->getHeaders(), 'params' => $app['request']->params()));

        $app['response']->setStatus($status);
        $app['response']->addHeaders(array('Content-Type', 'application/json'));

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
     *
     * @param  \Exception  $e
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
