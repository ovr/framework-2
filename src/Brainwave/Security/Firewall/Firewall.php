<?php namespace Brainwave\Security\Firewall;

use \Brainwave\Middleware\Middleware;
use \Brainwave\Security\Firewall\Matcher;
use \Brainwave\Security\Firewall\Interfaces\FirewallInterface;

/**
*
*/
class Firewall extends Middleware implements FirewallInterface
{
    /**
     * [$firewall description]
     * @var [type]
     */
    protected $firewall;

    public function __construct(array $options = [])
    {
        $this->matcher = new Matcher($options['firewalls']);
        unset($options['firewalls']);
        $this->options = $options;
    }

    /**
     * Call
     *
     */
    public function call()
    {
        # code...
    }
}