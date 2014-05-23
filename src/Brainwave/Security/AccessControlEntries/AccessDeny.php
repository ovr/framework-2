<?php namespace Brainwave\Security\AccessControlEntries;

use \Brainwave\Security\AccessControlEntries\AccessControlEntries;

/**
 *
 */
class AccessDeny extends AccessControlEntries
{
    /**
     * This AccessControlEntries always grants access by returning false.
     *
     * @param array $params
     * @return bool
     */
    public function isAllowed(array $params = array())
    {
        return false;
    }
}