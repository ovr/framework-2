<?php namespace Brainwave\Support;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Workbench\Workbench;
use \Brainwave\Support\FacadeManager;

/**
 *
 */
class Facades extends FacadeManager
{
    /**
     * [$brainwave description]
     * @var [type]
     */
    protected static $brainwave;

    /**
     * [$facadeAlias description]
     * @var [type]
     */
    protected $facadeAlias = array();

    /**
     * Description
     * @return type
     */
    public function __construct(Workbench $app)
    {
        static::setFacadeApplication($app);
        $this->registerAliases();
    }

    /**
     * Description
     * @param type array $facadeClass
     * @return type
     */
    public function registerFacade(array $facadeClass = array())
    {
        $this->facadeAlias = $facadeClass;
        return $this;
    }

    /**
     * Description
     * @return type
     */
    protected function getFacadeAlias()
    {
        return $this->facadeAlias;
    }

    /**
     * Register aliases
     * @param type $aliases
     * @return type
     */
    protected function registerAliases()
    {
        $aliases = array(
            'App'           => '\Brainwave\Support\Facades\App',
            'Log'           => '\Brainwave\Support\Facades\Log',
            'Auth'          => '\Brainwave\Support\Facades\Auth',
            'Mail'          => '\Brainwave\Support\Facades\Mail',
            'View'          => '\Brainwave\Support\Facades\View',
            'Event'         => '\Brainwave\Support\Facades\Event',
            'Route'         => '\Brainwave\Support\Facades\Route',
            'Config'        => '\Brainwave\Support\Facades\Config',
            'Request'       => '\Brainwave\Support\Facades\Request',
            'Response'      => '\Brainwave\Support\Facades\Response',
            'Services'      => '\Brainwave\Support\Facades\Services',
        );

        // If user pass some new facades from registerAliases function
        if (!empty($this->facadeAlias)) {
            foreach ($this->getFacadeAlias() as $alias => $class) {
                $aliases[$alias] = $class;
            }
        }

        foreach ($aliases as $alias => $class) {
            if (class_exists($class)) {
                class_alias($class, $alias);
            } else {
                throw new \RuntimeException('Class "' . $class . '" for alias "' . $alias . '" not exists.');
            }
        }
    }

    /**
     * [setFacadeApplication description]
     * @param Workbench $app [description]
     */
    protected static function setFacadeApplication(Workbench $app)
    {
        parent::$app = $app;
        self::$app = $app;

        self::$brainwave = $app;
    }
}
