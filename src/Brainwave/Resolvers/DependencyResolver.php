<?php namespace Brainwave\Resolvers;

use \Brainwave\Workbench\Workbench;
use \Brainwave\Resolvers\Interfaces\CallableResolverInterface;

/**
 *
 */
class DependencyResolver implements CallableResolverInterface
{
    /**
     * [$app description]
     * @var [type]
     */
    private $app;

    /**
     * [__construct description]
     * @param Pimple $container [description]
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
        if (is_string($callable) && preg_match('!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!', $callable, $matches)) {

            $service = $matches[1];
            $method = $matches[2];

            if (!isset($this->app[$service])) {
                throw new \InvalidArgumentException('Route key does not exist in Workbench');
            }

            $callable =  array($this->app[$service],$method);
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Route callable must be callable');
        }

        return $callable;
    }
}
