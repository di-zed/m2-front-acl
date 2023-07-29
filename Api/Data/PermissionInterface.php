<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Permission Interface.
 */
interface PermissionInterface extends ExtensibleDataInterface
{
    /**
     * Field ID.
     */
    const FIELD_ID = 'id';

    /**
     * Field Customer ID.
     */
    const FIELD_CUSTOMER_ID = 'customer_id';

    /**
     * Field Resource Type.
     */
    const FIELD_RESOURCE_TYPE = 'resource_type';

    /**
     * Field Resource ID.
     */
    const FIELD_RESOURCE_ID = 'resource_id';

    /**
     * Field Permission.
     */
    const FIELD_PERMISSION = 'permission';

    /**
     * Field Created At.
     */
    const FIELD_CREATED_AT = 'created_at';

    /**
     * Get customer ID.
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID.
     *
     * @param int $customerId
     * @return PermissionInterface
     */
    public function setCustomerId(int $customerId): PermissionInterface;

    /**
     * Get resource type.
     *
     * @return string
     */
    public function getResourceType(): string;

    /**
     * Set resource type.
     *
     * @param string $resourceType
     * @return PermissionInterface
     */
    public function setResourceType(string $resourceType): PermissionInterface;

    /**
     * Get resource ID.
     *
     * @return string
     */
    public function getResourceId(): string;

    /**
     * Set resource ID.
     *
     * @param string $resourceId
     * @return PermissionInterface
     */
    public function setResourceId(string $resourceId): PermissionInterface;

    /**
     * Get permission.
     *
     * @return string
     */
    public function getPermission(): string;

    /**
     * Set permission.
     *
     * @param string $permission
     * @return PermissionInterface
     */
    public function setPermission(string $permission): PermissionInterface;

    /**
     * Get create date.
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set create date.
     *
     * @param string $createdAt
     * @return PermissionInterface
     */
    public function setCreatedAt(string $createdAt): PermissionInterface;
}
