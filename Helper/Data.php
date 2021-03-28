<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Helper;

use DiZed\FrontAcl\Api\RoleManagementInterface;
use DiZed\FrontAcl\Model\Config\Source\Permissions as SourcePermissions;
use DiZed\FrontAcl\Model\Config\Source\ResourceTypes;
use DiZed\FrontAcl\Model\Config\Source\Roles as SourceRoles;
use DiZed\FrontAcl\Model\ResourceModel\Permission;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper Data.
 */
class Data extends AbstractHelper
{
    /**
     * Path for the module status.
     */
    const XML_PATH_ENABLED = 'dized_front_acl/general/enabled';

    /**
     * @var CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var SourceRoles
     */
    protected $sourceRoles;

    /**
     * @var SourcePermissions
     */
    protected $sourcePermissions;

    /**
     * @var RoleManagementInterface
     */
    protected $roleManagementInterface;

    /**
     * Helper constructor.
     *
     * @param Context $context
     * @param CollectionFactory $customerCollectionFactory
     * @param SourceRoles $sourceRoles
     * @param SourcePermissions $sourcePermissions
     * @param RoleManagementInterface $roleManagementInterface
     */
    public function __construct(
        Context $context,
        CollectionFactory $customerCollectionFactory,
        SourceRoles $sourceRoles,
        SourcePermissions $sourcePermissions,
        RoleManagementInterface $roleManagementInterface
    ) {
        parent::__construct($context);

        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->sourceRoles = $sourceRoles;
        $this->sourcePermissions = $sourcePermissions;
        $this->roleManagementInterface = $roleManagementInterface;
    }

    /**
     * Is module enabled?
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get role management interface.
     *
     * @return RoleManagementInterface
     */
    public function getRoleManagement(): RoleManagementInterface
    {
        return $this->roleManagementInterface;
    }

    /**
     * Get role list.
     *
     * @return array
     */
    public function getRoleList(): array
    {
        $roleOptions = $this->sourceRoles->toOptionArray();

        $roleList = [];
        foreach ($roleOptions as $roleOption) {
            if (!empty($roleOption['value'])) {
                $roleList[$roleOption['value']] = $roleOption['label'];
            }
        }
        asort($roleList);

        return $roleList;
    }

    /**
     * Get permission list.
     *
     * @param string $role
     * @return array
     */
    public function getPermissionList(string $role = ''): array
    {
        $rolePermissions = [];
        if (!empty($role)) {
            $rolePermissions = $this->getRoleManagement()->getDefaultPermissions($role);
        }

        $permissionOptions = $this->sourcePermissions->toOptionArray();

        $permissionList = [];
        foreach ($permissionOptions as $permissionOption) {
            if (!empty($permissionOption['value'])) {
                if (!empty($role) && empty($rolePermissions[$permissionOption['value']])) {
                    continue;
                }
                $permissionList[$permissionOption['value']] = $permissionOption['label'];
            }
        }
        asort($permissionList);

        return $permissionList;
    }

    /**
     * Get customer collection.
     *
     * @param array $roles
     * @param array $permissions
     * @return Collection
     */
    public function getCustomerCollection(array $roles, array $permissions = []): Collection
    {
        $collection = $this->customerCollectionFactory->create();

        if ($roles) {
            $collection->getSelect()->joinInner(
                ['role' => Permission::TABLE_NAME],
                implode(' AND ', [
                    '`role`.`customer_id` = e.entity_id',
                    '`role`.`resource_type` = "' . ResourceTypes::VALUE_ROLE . '"',
                    '`role`.`resource_id` IN ("' . implode('", "', $roles) . '")',
                    '`role`.`permission` = "' . RoleManagementInterface::ALLOW_PERMISSION . '"',
                ]),
                null
            );
        }

        if ($permissions) {
            $collection->getSelect()->joinInner(
                ['permission' => Permission::TABLE_NAME],
                implode(' AND ', [
                    '`permission`.`customer_id` = e.entity_id',
                    '`permission`.`resource_type` = "' . ResourceTypes::VALUE_PERMISSION . '"',
                    '`permission`.`resource_id` IN ("' . implode('", "', $permissions) . '")',
                    '`permission`.`permission` = "' . RoleManagementInterface::ALLOW_PERMISSION . '"',
                ]),
                null
            );
        }

        return $collection;
    }
}
