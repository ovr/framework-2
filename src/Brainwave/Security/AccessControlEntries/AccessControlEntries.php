<?php namespace Brainwave\Security\AccessControlEntries;

use \Brainwave\Security\Role\Interfaces\RoleInterface;
use \Brainwave\Security\AccessControlEntries\Exception\AccessControlEntriesException;
use \Brainwave\Security\AccessControlEntries\Interfaces\AccessControlEntriesInterface;

/**
 *
 */
abstract class AccessControlEntries implements AccessControlEntriesInterface
{
    /**
     * @var RoleInterface
     */
    protected $role;

    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var PermissionInterface[]
     */
    protected $permissions = array();

    /**
     * [$options description]
     * @var array
     */
    protected $options;

    /**
     * Construct new AccessControlEntries object with specified role, resource and permissions.
     *
     * @param RoleInterface $role
     * @param ResourceInterface $resource
     * @param PermissionInterface|PermissionInterface[] $permissions
     */
    public function __construct(RoleInterface $role = null, ResourceInterface $resource = null, $permissions = null)
    {
        if (isset($role)) {
            $this->setRole($role);
        }
        if (isset($resource)) {
            $this->setResource($resource);
        }
        if (isset($permissions) && !empty($permissions)) {
            $this->setPermissions($permissions);
        }
    }

    /**
     * [setRole description]
     * @param RoleInterface $role [description]
     */
    public function setRole(RoleInterface $role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * [setResource description]
     * @param ResourceInterface $resource [description]
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * [setPermissions description]
     * @param [type] $permissions [description]
     */
    public function setPermissions($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }
        foreach ($permissions as $permission) {
            if ($permission instanceof PermissionInterface) {
                $this->permissions[] = $permission;
            } else {
                throw new AccessControlEntriesException('Specified permission object does not implement PermissionInterface');
            }
        }
        return $this;
    }

    /**
     * {@interitDoc}
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
        return $this;
    }

    /**
     * {@interitDoc}
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * {@interitDoc}
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@interitDoc}
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * {@interitDoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }
}