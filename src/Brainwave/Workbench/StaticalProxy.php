<?php
namespace Brainwave\Workbench;

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
use \Brainwave\Workbench\StaticalProxyManager;

/**
 * StaticalProxy
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 *
 */
class StaticalProxy extends StaticalProxyManager
{
    /**
     * Brainwave\Workbench\Workbench
     * @var bool
     */
    protected static $brainwave;

    /**
     * All alias for statical proxy
     * @var [type]
     */
    protected $facadeAlias = [];

    /**
     * Set the Application Brainwave\Workbench\Workbench for statical proxy
     * @return boolean|null
     */
    public function __construct(Workbench $app)
    {
        self::setFacadeApplication($app);
    }

    /**
     * Register all system facades/proxys
     * @param type array $facadeClass
     * @return StaticalProxy
     */
    public function registerFacade(array $facadeClass = [])
    {
        $this->facadeAlias = $facadeClass;
        return $this;
    }

    /**
     * Get all registered facades/proxys
     * @return type
     */
    public function getFacadeAlias()
    {
        return $this->facadeAlias;
    }

    /**
     * Register aliases
     * @return type
     */
    public function registerAliases()
    {
        foreach ($this->getFacadeAlias() as $alias => $class) {
            if (class_exists($class)) {
                class_alias($class, $alias);
            } else {
                throw new \RuntimeException('Class "' . $class . '" for alias "' . $alias . '" not exists.');
            }
        }
    }

    /**
     * Set the Application
     * @param Workbench $app Brainwave\Workbench\Workbench
     */
    public static function setFacadeApplication($app)
    {
        parent::setFacadeApplication($app);

        self::$brainwave = $app;
    }
}
