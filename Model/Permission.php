<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model;

use DiZed\FrontAcl\Api\Data\PermissionInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Model for customer permissions.
 */
class Permission extends AbstractModel implements PermissionInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\DiZed\FrontAcl\Model\ResourceModel\Permission::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData(self::FIELD_CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $customerId): PermissionInterface
    {
        return $this->setData(self::FIELD_CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return (string)$this->getData(self::FIELD_RESOURCE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setResourceType(string $resourceType): PermissionInterface
    {
        return $this->setData(self::FIELD_RESOURCE_TYPE, $resourceType);
    }

    /**
     * @inheritdoc
     */
    public function getResourceId(): string
    {
        return (string)$this->getData(self::FIELD_RESOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setResourceId(string $resourceId): PermissionInterface
    {
        return $this->setData(self::FIELD_RESOURCE_ID, $resourceId);
    }

    /**
     * @inheritdoc
     */
    public function getPermission(): string
    {
        return (string)$this->getData(self::FIELD_PERMISSION);
    }

    /**
     * @inheritdoc
     */
    public function setPermission(string $permission): PermissionInterface
    {
        return $this->setData(self::FIELD_PERMISSION, $permission);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::FIELD_CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $createdAt): PermissionInterface
    {
        return $this->setData(self::FIELD_CREATED_AT, $createdAt);
    }
}
