<?php namespace Brainwave\Security\Role;

use \Brainwave\Security\Role\RoleInferface;

/**
*
*/
class RoleHandler implements RoleInferface
{
    /**
     * All roles
     * @var array
     */
    public $roles = array();

    /**
     * Set roles
     * @param array $roles
     */
    public function setRole($role)
    {
        $this->roles[$role] = $role;
        return $this;
    }

    /**
     * Set roles
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        foreach ($roles as $role) {
            $this->roles[$role] = $role;
        }

        return $this;
    }

    /**
     * Get roles
     * @return array returns all roles as a array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get role
     * @param string  $role Role name
     * @return string returns a special role
     */
    public function getRole($role)
    {
        return $this->roles[$role];
    }

    /**
     * Removes all roles
     * @return bool \Brainwave\Security\Role\Role
     */
    public function removeRoles()
    {
        unset($this->roles);
        return $this;
    }

    /**
     * Remove role
     * @param  string $role role name
     * @return bool   \Brainwave\Security\Role\Role
     */
    public function removeRole($role)
    {
        unset($this->roles[$role]);
        return $this;
    }
}
