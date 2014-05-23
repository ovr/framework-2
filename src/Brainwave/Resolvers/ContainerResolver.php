<?php namespace Brainwave\Resolvers;

use \Brainwave\Workbench\Workbench;
use \Brainwave\Resolvers\Interfaces\CallableResolverInterface;

/**
 *
 */
class appResolver implements CallableResolverInterface
{
    /**
     * [$app description]
     * @var [type]
     */
    private $app;

    /**
     * Description
     * @param type Workbench $app
     * @return type
     */
    public function __construct(Workbench $app)
    {
        $this->app = $app;
    }

    /**
     * [build description]
     * @param  [type] $callable [description]
     * @return [type]           [description]
     */
    public function build($callable)
    {
        $matches = array();
        if (is_string($callable) && preg_match('!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!', $callable, $matches)) {
            $class = $matches[1];
            $method = $matches[2];
            $app = $this->app;

            $callable = function() use ($class, $method, $app) {
                static $obj = null;
                if ($obj === null) {
                    $obj = new $class($app);
                }
                return call_user_func_array(array($obj, $method), func_get_args());
            };
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Route callable must be callable');
        }

        return $callable;
    }
}
