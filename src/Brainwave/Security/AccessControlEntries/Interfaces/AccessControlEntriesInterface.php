<?php namespace Brainwave\Security\AccessControlEntries\Interfaces;

use \Brainwave\Security\AccessControlEntries\Role\RoleInterface;

/**
 *
 */
interface AccessControlEntriesInterface
{
    /**
     * Set role which this AccessControlEntries is assinged to.
     * @param RoleInterface $role
     */
    public function setRole(RoleInterface $role);

    /**
     * Set resource which this AccessControlEntries is assinged to.
     * @param ResourceInterface $resource
     */
    public function setResource(ResourceInterface $resource);

    /**
     * Set permissions granted/revoked by this AccessControlEntries.
     * @param PermissionInterface|array $permissions
     */
    public function setPermissions($permissions);

    /**
     * Set additional options.
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Get role which this AccessControlEntries is assinged to.
     * @return RoleInterface
     */
    public function getRole();

    /**
     * Get resource which this AccessControlEntries is assinged to.
     * @return ResourceInterface
     */
    public function getResource();

    /**
     * Get permissions granted/revoked by this AccessControlEntries.
     * @return array
     */
    public function getPermissions();

    /**
     * Get additional options.
     * @return array
     */
    public function getOptions();

    /**
     * Return true if assigned permissions to the assigned resource are granted for assigned role or false otherwise.
     * @param array $params
     * @return bool
     */
    public function isAllowed(array $params = array());
}