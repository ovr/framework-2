<?php namespace Brainwave\Security\Firewall;

use \Brainwave\Http\Request;

/**
 *
 */
class Matcher
{
    /**
     * [$firewalls description]
     * @var [type]
     */
    private $firewalls = [];

    /**
     * [__construct description]
     * @param array $firewalls [description]
     */
    public function __construct(array $firewalls)
    {
        if (!is_null($firewalls)) {
            foreach ($firewalls as $firewall) {
                // Set default values
                $firewall = $firewall + [
                    'anonymous' => false,
                    'exact_match' => false,
                    'method' => null
                ];
                $this->firewalls[] = $firewall;
            }

            // We want to sort things by more specific paths first. This will
            // ensure that for instance '/' is never captured before any other
            // firewalled paths.
            uasort($this->firewalls, function($a, $b) {
                if ($a['path'] === $b['path']) {
                    return 0;
                }
                return -($a > $b ? 1 : -1);
            });
        }
    }

    /**
     * Find the matching path
     */
    public function match(Request $request)
    {
        foreach ($this->firewalls as $firewall) {
            if ($firewall['method'] !== null && $request->getMethod() !== $firewall['method']) {
                continue;
            }

            if ($firewall['exact_match']) {
                if ($request->getPathInfo() === $firewall['path']) {
                    return $firewall;
                }
            } elseif (0 === strpos($request->getPathInfo(), $firewall['path'])) {
                return $firewall;
            }
        }

        return null;
    }
}