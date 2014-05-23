<?php namespace Brainwave\Security\Role\Interfaces;

/**
*
*/
Interface RoleInterface
{

    /**
     * Set roles
     * @param array $roles
     */
    public function setRole($role);

    /**
     * Set roles
     * @param array $roles
     */
    public function setRoles(array $roles);

    /**
     * Get roles
     * @return array returns all roles as a array
     */
    public function getRoles();

    /**
     * Get role
     * @param string  $role Role name
     * @return string returns a special role
     */
    public function getRole($role);

    /**
     * Removes all roles
     * @return bool \Brainwave\Security\Role\Role
     */
    public function removeRoles();

    /**
     * Remove role
     * @param  string $role role name
     * @return bool   \Brainwave\Security\Role\Role
     */
    public function removeRole($role);
}
