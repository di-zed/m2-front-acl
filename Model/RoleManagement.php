<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model;

use DiZed\FrontAcl\Api\RoleManagementInterface;
use DiZed\FrontAcl\Model\Config\Source\DefaultRelations;
use DiZed\FrontAcl\Model\Config\Source\Permissions as SourcePermissions;
use DiZed\FrontAcl\Model\Config\Source\ResourceTypes;
use DiZed\FrontAcl\Model\Config\Source\Roles as SourceRoles;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Role Management for Front Acl module.
 */
class RoleManagement implements RoleManagementInterface
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var DefaultRelations
     */
    protected $sourceDefaultRelations;

    /**
     * @var SourcePermissions
     */
    protected $sourcePermissions;

    /**
     * @var SourceRoles
     */
    protected $sourceRoles;

    /**
     * @var PermissionFactory
     */
    protected $permissionFactory;

    /**
     * @var PermissionRepository
     */
    protected $permissionRepository;

    /**
     * Role Management constructor.
     *
     * @param TimezoneInterface $timezone
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerFactory $customerFactory
     * @param Session $customerSession
     * @param DefaultRelations $sourceDefaultRelations
     * @param SourcePermissions $sourcePermissions
     * @param SourceRoles $sourceRoles
     * @param PermissionFactory $permissionFactory
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(
        TimezoneInterface $timezone,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        Session $customerSession,
        DefaultRelations $sourceDefaultRelations,
        SourcePermissions $sourcePermissions,
        SourceRoles $sourceRoles,
        PermissionFactory $permissionFactory,
        PermissionRepository $permissionRepository
    ) {
        $this->timezone = $timezone;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->sourceDefaultRelations = $sourceDefaultRelations;
        $this->sourcePermissions = $sourcePermissions;
        $this->sourceRoles = $sourceRoles;
        $this->permissionFactory = $permissionFactory;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerSession(): Session
    {
        return $this->customerSession;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerData($customer = null): ?CustomerInterface
    {
        try {
            if (!($customer instanceof CustomerInterface)) {
                if (($customer instanceof Customer) && $customer->getId()) {
                    $customer = $this->customerRepository->getById($customer->getId());
                } elseif (is_numeric($customer) && $customer > 0) {
                    $customer = $this->customerRepository->getById($customer);
                } elseif ($customer === null) {
                    $customer = $this->customerSession->getCustomerData();
                }
            }
            if (!$customer || !$customer->getId()) {
                $customer = null;
            }
        } catch (\Exception $e) {
            $customer = null;
        }

        return $customer;
    }

    /**
     * @inheritdoc
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @inheritdoc
     */
    public function validateRole(string $role): bool
    {
        foreach ($this->sourceRoles->toOptionArray() as $option) {
            if ($option['value'] == $role) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasRole(string $role, $customer = null): bool
    {
        return ($this->getRole($customer) == $role);
    }

    /**
     * @inheritdoc
     */
    public function getRole($customer = null): string
    {
        $isCustomerFromSession = ($customer === null);

        if ($customer = $this->getCustomerData($customer)) {
            // get info from session:
            if ($isCustomerFromSession) {
                if ($sessionRole = $this->customerSession->getRole()) {
                    return $sessionRole;
                }
            }
            /** @var \DiZed\FrontAcl\Model\Permission[] $roleItems */
            $roleItems = $this->permissionRepository->getRoleItems($customer->getId());
            foreach ($roleItems as $roleItem) {
                return $roleItem->getResourceId();
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRoleName($customer = null): string
    {
        if ($role = $this->getRole($customer)) {
            return $this->sourceRoles->getOptionText($role);
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function resetRole($customer = null): bool
    {
        if (!$customer = $this->getCustomerData($customer)) {
            return false;
        }

        /** @var \DiZed\FrontAcl\Model\Permission[] $roleItems */
        $roleItems = $this->permissionRepository->getRoleItems($customer->getId());
        foreach ($roleItems as $roleItem) {
            try {
                $this->permissionRepository->delete($roleItem);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function setRole(string $role, $customer = null): bool
    {
        if (!$customer = $this->getCustomerData($customer)) {
            return false;
        }
        if (!$this->resetRole($customer)) {
            return false;
        }
        if (!$this->validateRole($role)) {
            return false;
        }

        try {
            $permission = $this->permissionFactory->create();
            $permission->setCustomerId($customer->getId());
            $permission->setResourceType(ResourceTypes::VALUE_ROLE);
            $permission->setResourceId($role);
            $permission->setPermission(self::ALLOW_PERMISSION);
            $permission->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
            $permission = $this->permissionRepository->save($permission);
            if (!$permission || !$permission->getId()) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function validatePermissions(array $permissions): bool
    {
        if (!$permissions) {
            return false;
        }

        $optionValues = [];
        foreach ($this->sourcePermissions->toOptionArray() as $option) {
            $optionValues[] = $option['value'];
        }

        foreach ($permissions as $permission) {
            if (!in_array($permission, $optionValues)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasPermission(string $permission, $customer = null): bool
    {
        if ($customer = $this->getCustomerData($customer)) {
            // check role:
            if (!$role = $this->getRole($customer)) {
                return false;
            }
            if (!$this->validateRole($role)) {
                return false;
            }
            if (!$this->validatePermissions([$permission])) {
                return false;
            }
            // check permissions:
            $permissions = $this->getPermissions($customer);
            return in_array($permission, $permissions);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($customer = null): array
    {
        $result = [];
        $isCustomerFromSession = ($customer === null);

        if ($customer = $this->getCustomerData($customer)) {
            // get info from session:
            if ($isCustomerFromSession) {
                if ($sessionPermissions = $this->customerSession->getPermissions()) {
                    return $sessionPermissions;
                }
            }
            /** @var \DiZed\FrontAcl\Model\Permission[] $permissionItems */
            $permissionItems = $this->permissionRepository->getPermissionItems($customer->getId());
            foreach ($permissionItems as $permissionItem) {
                if ($permissionItem->getPermission() == self::ALLOW_PERMISSION) {
                    $result[] = $permissionItem->getResourceId();
                }
            }
            asort($result);
        }

        return array_values($result);
    }

    /**
     * @inheritdoc
     */
    public function getPermissionNames($customer = null): array
    {
        $result = [];

        foreach ($this->getPermissions($customer) as $permission) {
            $result[] = $this->sourcePermissions->getOptionText($permission);
        }
        asort($result);

        return array_values($result);
    }

    /**
     * @inheritdoc
     */
    public function resetPermissions($customer = null): bool
    {
        if (!$customer = $this->getCustomerData($customer)) {
            return false;
        }

        /** @var \DiZed\FrontAcl\Model\Permission[] $permissionItems */
        $permissionItems = $this->permissionRepository->getPermissionItems($customer->getId());
        foreach ($permissionItems as $permissionItem) {
            try {
                $this->permissionRepository->delete($permissionItem);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function setPermissions(array $permissions, $customer = null): bool
    {
        if (!$customer = $this->getCustomerData($customer)) {
            return false;
        }

        if (!$this->resetPermissions($customer)) {
            return false;
        }

        $isAssocArray = !array_key_exists(0, $permissions);
        if (!$this->validatePermissions($isAssocArray ? array_keys($permissions) : $permissions)) {
            return false;
        }

        foreach ($permissions as $resourceId => $isAllowed) {
            // if $permissions is not an associate array:
            if (!$isAssocArray) {
                $resourceId = $isAllowed;
                $isAllowed = true;
            }
            try {
                $permission = $this->permissionFactory->create();
                $permission->setCustomerId((int)$customer->getId());
                $permission->setResourceType(ResourceTypes::VALUE_PERMISSION);
                $permission->setResourceId($resourceId);
                $permission->setPermission($isAllowed ? self::ALLOW_PERMISSION : self::DENY_PERMISSION);
                $permission->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                $permission = $this->permissionRepository->save($permission);
                if (!$permission || !$permission->getId()) {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultPermissions(string $role = ''): array
    {
        $permissions = [];
        foreach ($this->sourceDefaultRelations->toOptionArray() as $option) {
            $roleId = $this->sourceDefaultRelations->getRealRoleId($option['value']);
            $permissionId = $this->sourceDefaultRelations->getRealPermissionId($option['value']);
            if (!array_key_exists($roleId, $permissions)) {
                $permissions[$roleId] = [];
            }
            $permissions[$roleId][$permissionId] = true;
        }

        if (!empty($role)) {
            $permissions = (!empty($permissions[$role]) ? $permissions[$role] : []);
        }

        return $permissions;
    }
}
