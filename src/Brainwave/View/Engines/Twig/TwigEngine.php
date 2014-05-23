<?php namespace Brainwave\View\Engines\Twig;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Twig_Environment;
use \Assetic\AssetWriter;
use \Assetic\FilterManager;
use \Twig_Loader_Filesystem;
use \Assetic\Filter\LessFilter;
use \Assetic\Filter\CSSMinFilter;
use \Assetic\Factory\AssetFactory;
use \Brainwave\Workbench\Workbench;
use \Assetic\Filter\UglifyJs2Filter;
use \Assetic\Factory\LazyAssetManager;
use \Assetic\Extension\Twig\TwigResource;
use \Assetic\Extension\Twig\AsseticExtension;
use \Assetic\Extension\Twig\TwigFormulaLoader;
use \Assetic\Factory\Worker\CacheBustingWorker;
use \Brainwave\View\Engines\Interfaces\EnginesInterface;

/**
 *
 */
class TwigEngine implements EnginesInterface
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
     * [$loadedExtensions description]
     * @var [type]
     */
    protected $loadedExtensions = array();

    /**
     * All available extensions
     * @var array
     */
    protected $availableExtensions = array();

    /**
     * [$filterManger description]
     * @var [type]
     */
    protected $filterManger;

    /**
     * Create a new view environment instance.
     * @param  \Brainwave\Workbench\Workbench
     * @return void
     */
    public function __construct(Workbench $app)
    {
        $this->app = $app;

        // Check all needed twig settings
        if (is_null($this->app->config('twig.assets'))) {
            throw new \InvalidArgumentException('Set needed setting for twig. "twig.assets"');
        } elseif(is_null($this->app->config('twig.assetic.cache'))) {
            throw new \InvalidArgumentException('Set needed setting for twig. "twig.assetic.cache');
        } elseif(is_null($this->app->config('twig.environment'))) {
            throw new \InvalidArgumentException('Set needed setting for twig. "twig.environment');
        } elseif(is_null($this->app->config('twig.extensions'))) {
            throw new \InvalidArgumentException('Set needed setting for twig. "twig.extensions"');
        } elseif(is_null($this->app->config('twig.available_extensions'))) {
            throw new \InvalidArgumentException('Set needed setting for twig "twig.available_extensions"');
        } elseif(is_null($this->app->config('twig.extensions_enable_only'))) {
            throw new \InvalidArgumentException('Set needed setting for twig. "twig.extensions_enable_only"');
        } elseif(is_null($this->app->config('twig.assetic.filter'))) {
            throw new \InvalidArgumentException('Set needed setting for twig. "twig.assetic.filter"');
        }

        if (!is_null($this->app->config('twig.available_extensions'))) {
            $this->availableExtensions = $this->app->config('twig.available_extensions');
        }

        if(!is_null($this->app->config('twig.assetic.filter'))){
            $this->asseticFilter($this->app->config('twig.assetic.filter'));
        } else {
            $this->asseticFilter();
        }
    }

    /**
     * Twig paths
     * @return twig paths
     */
    protected function loader()
    {
        $loader = new Twig_Loader_Filesystem($this->app->config('view.default.template.path'));
        if (!is_null($this->app->config('view.template.paths'))) {
            foreach ($this->app->config('view.template.paths') as $name => $addPaths) {
                $loader->addPath($addPaths, $name);
            }
        }
        return $loader;
    }

    /**
     * [environment description]
     */
    protected function environment()
    {
        if (!is_null($this->app->config('twig.environment'))) {
            $environment = array_merge($this->app->config('twig.environment'), $this->app->config('view.cache'));
        } else {
            $environment = $this->app->config('view.cache');
        }

        $twig_environment = new Twig_Environment($this->loader(), $environment);

        // load all extension
        if (!is_null($this->loadedExtensions)) {
            foreach ($this->loadedExtensions as $extension) {
                $twig_environment->addExtension($extension);
            }
        }

        return $twig_environment;
    }

    /**
     * [asseticFilter description]
     * @param  array  $filters [description]
     * @return [type]          [description]
     */
    protected function asseticFilter(array $filters = array())
    {
        $this->filterManger = new FilterManager();
        $this->filterManger->set('cssmin', new CSSMinFilter());
        $this->filterManger->set('less', new LessFilter());
        $this->filterManger->set('uglifyjs2', new UglifyJs2Filter());

        foreach ($filters as $twigfiltername => $filter) {
            $this->filterManger->set($twigfiltername, $filter);
        }
    }

    /**
     * [asseticFactory description]
     * @return [type] [description]
     */
    protected function asseticFactory()
    {
        $assetFactory = new AssetFactory($this->app->config('twig.asset'));
        $assetFactory->setDebug($this->app->config('twig.environment')['debug']);

        $assetFactory->setFilterManager($this->filterManger);
        if (!is_null($this->app->config('twig.assetic.cache'))) {
            $cache = $this->app->config('twig.assetic.cache');
            $assetFactory->addWorker(new CacheBustingWorker($cache));
        } else {
            $assetFactory->addWorker(new CacheBustingWorker());
        }
        return $assetFactory;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param  array   $data
     * @return string
     */
    public function get(array $data = array())
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
        // get all extensions
        if (!is_null($this->app->config('twig.extensions_enable_only'))) {
            $this->loadedExtensions = array();
            foreach ($this->app->config('twig.extensions_enable_only') as $ext) {
                if (!in_array($ext, $this->availableExtensions)) {
                        throw new \InvalidArgumentException(sprintf('Extension with name "%s" is invalid.', $ext));
                }
                $this->loadedExtensions[] = $ext;
            }
        } else {
            //Get extension from configs
            if (!is_null($this->app->config('twig.extensions'))) {
                $twig_extension = array_merge($this->app->config('twig.extensions'), $this->availableExtensions);
            } else {
                $twig_extension = $this->availableExtensions;
            }

            $this->loadedExtensions = $twig_extension;
        }

        $assetManger = new LazyAssetManager($this->asseticFactory());
        $assetManger->setLoader('twig', new TwigFormulaLoader($this->environment()));

        $templates   = array();
        $directories = $this->app->config('view.template.paths');
        while (sizeof($directories)) {
            $directory = array_pop($directories);

            foreach(glob($directory."/*") as $file_path) {
                if (is_dir($file_path) === true) {
                    array_push($directories, $file_path);
                } elseif (is_file($file_path) === true && preg_match("/\.(html)$/", $file_path) == true) {
                    $templates[] = str_replace($this->app->config('twig.default.template.path'), '', $file_path);
                }
            }
        }

        foreach ($templates as $template) {
            $resource = new TwigResource(new \Twig_Loader_Filesystem($this->app->config('twig.default.template.path')), $template);
            $assetManger->addResource($resource, 'twig');
        }

        $assetWriter = new AssetWriter($this->app->root());
        $assetWriter->writeManagerAssets($assetManger);

        // We'll evaluate the contents of the view inside a try/catch block so we can
        // flush out any stray output that might get out before an error occurs or
        // an exception is thrown. This prevents any partial views from leaking.
        ob_start();
        try {
            return $this->environment()->render($path, $data);
        } catch (\Exception $e) {
            $this->handleViewException($e);
        }
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
