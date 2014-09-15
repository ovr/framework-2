<?php
namespace Brainwave\Workbench;

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
    protected $facadeAlias = array();

    /**
     * Set the Application Brainwave\Workbench\Workbench for statical proxy
     * @return bool
     */
    public function __construct(Workbench $app)
    {
        static::setFacadeApplication($app);
    }

    /**
     * Register all system facades/proxys
     * @param type array $facadeClass
     * @return Brainwave\Workbench\StaticalProxy
     */
    public function registerFacade(array $facadeClass = array())
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
     * @param type $aliases
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
    protected static function setFacadeApplication(Workbench $app)
    {
        parent::$app = $app;
        self::$app = $app;

        self::$brainwave = $app;
    }
}
