<?php
namespace Brainwave\View\Engines\Plates;

/**
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2014 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.8.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Narrowspark is an open source PHP 5 framework, based on the Slim framework.
 *
 */

use \League\Plates\Engine;
use \League\Plates\Template;
use \League\Plates\Extension\URI;
use \League\Plates\Extension\Asset;
use \Brainwave\Workbench\Workbench;
use \Brainwave\View\Engines\Interfaces\EnginesInterface;


/**
 * PlatesEngine
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class PlatesEngine implements EnginesInterface
{
    /**
     * Workbench
     * @var void
     */
    protected $app;

    /**
     * Set Path
     * @var string
     */
    protected $path;

    /**
     * [$engine description]
     * @var [type]
     */
    protected $engine;

    /**
     * All available extensions
     * @var array
     */
    protected $availableExtensions = array();

    /**
     * Create a new view environment instance.
     * @param  \Brainwave\Workbench\Workbench
     * @return void
     */
    public function __construct(Workbench $app)
    {
        $this->app = $app;

        // Check all needed plates settings
        // if (is_null($this->app->config('plates.extensions'))) {
        //     throw new \InvalidArgumentException('Set needed setting for plates. "plates.extensions"');
        // }

        if (!is_null($this->app->config('plates.extensions'))) {
            $this->availableExtensions = $this->app->config('plates.extensions');
        }

        //Engine
        $this->loader();
    }

    /**
     * Plates paths
     */
    protected function loader()
    {
        $engine = new Engine($this->app->config('view.default.template.path'));

        if (!is_null($this->app->config('view.template.paths'))) {
            foreach ($this->app->config('view.template.paths') as $name => $addPaths) {
                $engine->addFolder($name, $addPaths);
            }
        }

        $engine->setFileExtension(null);

        // Engine
        $this->engine = $engine;
    }

    /**
    * Get the evaluated contents of the view.
    * @param  array   $data
    * @return string
    */
    public function get(array $data = array())
    {
        return $this->evaluatePath($this->path, $data);
    }

   /**
    * Set path
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
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    protected function evaluatePath($path, array $data)
    {
        $engine = $this->engine;

        // Set uri extensions
        $engine->loadExtension(new URI($this->app['request']->getPathInfo()));

        // Set asset extensions
        $engine->loadExtension(new Asset($this->app->config('view.asset')));

        // Get all extensions
        if (!is_null($this->app->config('plates.extensions'))) {
            foreach ($this->availableExtensions as $ext) {
                $this->engine->loadedExtensions($ext);
            }
        }

        // Creat a new template
        $template = new Template($this->engine);

        if (!$this->engine->pathExists($path)) {
            throw new \Exception('Template "'.$path.'" dont exist!');
        }

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();
        try {
            return $template->render($path, $data);
        } catch (\Exception $e) {
            $this->handleViewException($e);
        }
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
