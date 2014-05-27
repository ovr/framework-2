<?php
namespace Brainwave\Config;

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

use \Brainwave\Config\Interfaces\ConfigurationInterface;
use \Brainwave\Config\Interfaces\ConfigurationHandlerInterface;

/**
 * Configuration
 * Uses a ConfigurationHandler class to parse configuration data, accessed as an array.
 *
 * @package Narrowspark/framework
 * @author  Daniel Bannert
 * @since   0.8.0-dev
 */
class Configuration implements ConfigurationInterface, \IteratorAggregate
{
    /**
     * Handler for Configuration values
     * @var mixed
     */
    protected $handler;
    /**
     * Storage array of values
     * @var array
     */
    protected $values = array();

    /**
     * Default values
     * @var array
     */
    protected $defaults = array(
        // Application
        'app.locale' => 'en',
        'app.charset' => 'UTF-8',
        'app.footer' => 'cresk',
        'app.default.configs' => array(
            'mail',
            'cache',
            'provides',
            'template'
        ),
        // Provider
        'app.provides' => array(),
        'app.defaultProviders' => array(
            //'\Brainwave\Log\LoggerServiceProvider' => array(),
            '\Brainwave\Crypt\CryptServiceProvider' => array(),
            '\Brainwave\Session\SessionServiceProvider' => array(),
            '\Brainwave\Flash\FlashServiceProvider' => array(),
            '\Brainwave\Support\Translator\TranslatorServiceProvider' => array(),
            '\Brainwave\Event\EventServiceProvider' => array(),
            '\Brainwave\Cache\CacheServiceProvider' => array()
        ),
        // Cookies
        'cookies.encrypt' => false,
        'cookies.lifetime' => '20 minutes',
        'cookies.path' => '/',
        'cookies.domain' => null,
        'cookies.secure' => false,
        'cookies.httponly' => false,
        // Encryption
        'crypt.key' => 'A9s_lWeIn7cML8M]S6Xg4aR^GwovA&UN',
        'crypt.cipher' => MCRYPT_RIJNDAEL_256,
        'crypt.mode' => 'ctr',
        // Session
        'session.handler' => null,
        'session.cookies' => array(),
        'session.flash_key' => 'app.flash',
        'session.encrypt' => false,
        // HTTP
        'http.version' => '1.1',
        // Routing
        'routes.case_sensitive' => true,
        'routes.context' => null,
        'routes.route_class' => '\Brainwave\Routing\Route',
        // View
        'view.engine' => array('plates' => '\Brainwave\View\Engines\Plates\PlatesEngine'),
        'view.default.template.path' => '',
        'view.template.paths' => array(),
        'view.items' => array(),
        'view.cache' => '',
        'view.asset' => '',
        'view.extensions' => '.html',
        // Json
        'json.option' => '0',
        // Database setting
        'db.options' => array(
            'dsn'      => '',
            'username' => '',
            'password' => '',
            'frozen'   => false
        ),
        //Callable Resolver
        'callable.resolver' => 'CallableResolver'
    );

     /**
     * Constructor
     * @param mixed $handler
     */
    public function __construct(ConfigurationHandlerInterface $handler)
    {
        $this->setHandler($handler);
    }

    /**
     * Set Brainwave's defaults using the handler
     */
    public function setArray(array $values)
    {
        $this->handler->setArray($values);
    }

    /**
     * Set Brainwave's defaults using the handler
     */
    public function setDefaults()
    {
        $this->handler->setArray($this->defaults);
    }

    /**
     * Get the default settings
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Set a configuration handler and provide it some defaults
     * @param \Brainwave\Config\Interfaces\ConfigurationHandlerInterface $handler
     */
    public function setHandler(ConfigurationHandlerInterface $handler)
    {
        $this->handler = $handler;
        $this->setDefaults();
    }

    /**
     * Get the configuration handler for access
     * @return \Brainwave\Config\Interfaces\ConfigurationHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Get a value
     * @param  string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->handler[$key];
    }

    /**
     * Set a value
     * @param  string $key
     * @param  mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->handler[$key] = $value;
    }

    /**
     * Check a value exists
     * @param  string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->handler[$key]);
    }

    /**
     * Remove a value
     * @param  string $key
     */
    public function offsetUnset($key)
    {
        unset($this->handler[$key]);
    }

    /**
     * Get an ArrayIterator for the stored items
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
