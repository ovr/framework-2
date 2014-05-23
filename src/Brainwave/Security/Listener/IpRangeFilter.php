<?php namespace Brainwave\Security\Listener;

/*
 * This file is part of Brainwave.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Brainwave\Security\Listener\Interfaces\IpRangeFilterInterface;

/**
*
*/
class IpRangeFilter implements IpRangeFilterInterface
{
    /**
     * [$blacklist description]
     * @var [type]
     */
    protected $blacklist;

    /**
     * [$whitelist description]
     * @var [type]
     */
    protected $whitelist;

    /**
     * [setIpBlacklist description]
     * @param array $blacklist [description]
     */
    public function setIpBlacklist(array $blacklist = array())
    {
        $this->blacklist = $blacklist;
        return $this;
    }

    /**
     * [getIpBlacklist description]
     * @return [type] [description]
     */
    public function getIpBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * [setIpWhitelist description]
     * @param array $whitelist [description]
     */
    public function setIpWhitelist(array $whitelist = array())
    {
        $this->whitelist = $blacklist;
        return $this;
    }

    /**
     * [getIpWhitelist description]
     * @return [type] [description]
     */
    public function getIpWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * Return boolean if IP is valid given a certain wildcard pattern or ip range
     *
     * @param  string  $ip
     * @param  string  $pattern
     * @return boolean
     */
    public function isIpInRange($ip, $pattern)
    {
        $ip = ip2long($ip);
        $range = (false === strpos($pattern, ":")) ? $this->getRangeIPv4($pattern) : $this->getRangeIPv6($pattern);

        return ($ip >= $range['start'] && $ip <= $range['end']);
    }

    protected function getRangeIPv4($pattern)
    {
        //check for explicit range first
        if (count($exp = explode("-", $pattern)) == 2) {
            return array(
                'start' => ip2long($exp[0]),
                'end' => ip2long($exp[1])
            );
        }

        //if no wildcards, it's a regular ip, so start/end are same
        if (false === strpos($pattern, "*")) {
            return array(
                'start' => ip2long($pattern),
                'end' => ip2long($pattern)
            );
        }

        //check wildcards
        $start = array();
        $end = array();
        foreach (explode(".", $pattern) as $section) {
            if ($section === '*') {
                $start[] = "0";
                $end[] = "255";
            } else {
                $start[] = $section;
                $end[] = $section;
            }
        }

        return array(
            'start' => ip2long(implode(".", $start)),
            'end' => ip2long(implode(".", $end))
        );
    }

    protected function getRangeIPv6($pattern)
    {
        //TODO
    }
}