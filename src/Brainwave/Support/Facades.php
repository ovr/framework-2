<?php
namespace Brainwave\Support;

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
use \Brainwave\Support\FacadeManager;

/**
 * Facades
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
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
