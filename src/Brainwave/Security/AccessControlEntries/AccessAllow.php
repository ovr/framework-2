<?php namespace Brainwave\Security\AccessControlEntries;

use \Brainwave\Security\AccessControlEntries\AccessControlEntries;

/**
 *
 */
class AccessAllow extends AccessControlEntries
{
    /**
     * This AccessControlEntries always grants access by returning true.
     *
     * @param array $params
     * @return bool
     */
    public function isAllowed(array $params = array())
    {
        return true;
    }
}