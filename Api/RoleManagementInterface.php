<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;

/**
 * Role Management Interface.
 */
interface RoleManagementInterface
{
    /**
     * Allow permission name.
     */
    const ALLOW_PERMISSION = 'allow';

    /**
     * Deny permission name.
     */
    const DENY_PERMISSION = 'deny';

    /**
     * Get customer session.
     *
     * @return Session
     */
    public function getCustomerSession(): Session;

    /**
     * Get customer model object.
     *
     * @param mixed $customer
     * @return CustomerInterface|null
     */
    public function getCustomerData($customer = null): ?CustomerInterface;

    /**
     * Is customer logged in?
     *
     * @return bool
     */
    public function isCustomerLoggedIn(): bool;

    /**
     * Validate role.
     *
     * @param string $role
     * @return bool
     */
    public function validateRole(string $role): bool;

    /**
     * Check has customer this role or not.
     *
     * @param string $role
     * @param mixed $customer
     * @return bool
     */
    public function hasRole(string $role, $customer = null): bool;

    /**
     * Get customer role.
     *
     * @param mixed $customer
     * @return string
     */
    public function getRole($customer = null): string;

    /**
     * Get customer role name.
     *
     * @param mixed $customer
     * @return string
     */
    public function getRoleName($customer = null): string;

    /**
     * Reset role for a customer.
     *
     * @param mixed $customer
     * @return bool
     */
    public function resetRole($customer = null): bool;

    /**
     * Set role for a customer.
     *
     * @param string $role
     * @param mixed $customer
     * @return bool
     */
    public function setRole(string $role, $customer = null): bool;

    /**
     * Validate permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function validatePermissions(array $permissions): bool;

    /**
     * Check has customer this permission or not.
     *
     * @param string $permission
     * @param mixed $customer
     * @return bool
     */
    public function hasPermission(string $permission, $customer = null): bool;

    /**
     * Get customer permissions.
     *
     * @param mixed $customer
     * @return array
     */
    public function getPermissions($customer = null): array;

    /**
     * Get customer permission names.
     *
     * @param mixed $customer
     * @return array
     */
    public function getPermissionNames($customer = null): array;

    /**
     * Reset permissions for a customer.
     *
     * @param mixed $customer
     * @return bool
     */
    public function resetPermissions($customer = null): bool;

    /**
     * Set permissions for a customer.
     *
     * @param array $permissions
     * @param mixed $customer
     * @return bool
     */
    public function setPermissions(array $permissions, $customer = null): bool;

    /**
     * Get default permissions for all roles or for one role.
     *
     * @param string $role
     * @return array
     */
    public function getDefaultPermissions(string $role = ''): array;
}
