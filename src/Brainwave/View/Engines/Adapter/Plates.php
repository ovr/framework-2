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
 */

use Brainwave\View\Engines\Interfaces\EngineInterface as EnginesContract;
use League\Plates\Engine;
use League\Plates\Extension\Asset;
use League\Plates\Extension\URI;
use League\Plates\Template\Template;
use Pimple\Container;

/**
 * Plates
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class Plates implements EnginesContract
{
    /**
     * Container
     *
     * @var \Pimple\Container
     */
    protected $container;

    /**
     * [$engine description]
     * @var [type]
     */
    protected $engine;

    /**
     * All available extensions
     *
     * @var array
     */
    protected $availableExtensions = [];

    /**
     * Create a new view environment instance.
     *
     * @param \Pimple\Container $container
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        if ($this->container['settings']->get('view::plates.extensions', null) !== null) {
            $this->availableExtensions = $this->container['settings']['view::plates.extensions'];
        }

        //Engine
        $this->loader();
    }

    /**
     * Plates paths
     */
    protected function loader()
    {
        $engine = new Engine($this->container['settings']->get('view::default.template.path', null));

        if (!is_null($this->container['settings']->get('view::template.paths', null))) {
            foreach ($this->container['settings']->get('view::template.paths', null) as $name => $addPaths) {
                $engine->addFolder($name, $addPaths);
            }
        }

        $engine->setFileExtension(null);

        // Engine
        $this->engine = $engine;
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
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    protected function evaluatePath($path, array $data)
    {
        $engine = $this->engine;

        // Set uri extensions
        $engine->loadExtension(new URI($this->container['request']->getPathInfo()));

        // Set asset extensions
        $engine->loadExtension(new Asset($this->container['settings']->get('view::asset', null)));

        // Get all extensions
        if (!is_null($this->container['settings']->get('view::plates.extensions', null))) {
            foreach ($this->availableExtensions as $ext) {
                $this->engine->loadExtensions($ext);
            }
        }

        // Creat a new template
        $template = new Template($this->engine, $path);

        if (!$this->engine->exists($path)) {
            throw new \Exception('Template "'.$path.'" dont exist!');
        }

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();

        try {
            return $template->render($data);
        } catch (\Exception $e) {
            $this->handleViewException($e);
        }
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
    protected function handleViewException(\Exception $e)
    {
        ob_get_clean();
        throw $e;
    }
}
