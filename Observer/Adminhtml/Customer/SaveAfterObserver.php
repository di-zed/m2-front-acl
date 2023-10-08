<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Observer\Adminhtml\Customer;

use DiZed\FrontAcl\Api\RoleManagementInterface;
use DiZed\FrontAcl\Helper\Data;
use DiZed\FrontAcl\Model\Config\Source\ResourceTypes;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for "adminhtml_customer_save_after" event.
 * Adding permission rules to the customer.
 *
 * @see \Magento\Customer\Controller\Adminhtml\Index\Save::execute
 */
class SaveAfterObserver implements ObserverInterface
{
    /**
     * @var RoleManagementInterface
     */
    protected $roleManagement;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Observer constructor.
     *
     * @param RoleManagementInterface $roleManagement
     * @param Data $helper
     */
    public function __construct(
        RoleManagementInterface $roleManagement,
        Data $helper
    ) {
        $this->roleManagement = $roleManagement;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isModuleEnabled()) {
            return;
        }

        /** @var Http $request */
        $request = $observer->getRequest();

        if ($this->isFrontAclLoaded($request)) {
            /** @var Customer $customer */
            $customer = $observer->getCustomer();
            // set role for customer:
            if ($role = $this->getPostRole($request)) {
                $this->roleManagement->setRole($role, $customer);
            } else {
                $this->roleManagement->resetRole($customer);
            }
            // set permissions for customer:
            if ($permissions = $this->getPostPermissions($request)) {
                $this->roleManagement->setPermissions($permissions, $customer);
            } else {
                $this->roleManagement->resetPermissions($customer);
            }
        }
    }

    /**
     * Is Front ACL loaded?
     *
     * @param Http $request
     * @return bool
     */
    protected function isFrontAclLoaded(Http $request): bool
    {
        return (bool)$request->getParam('is_front_acl_loaded');
    }

    /**
     * Get customer role from request.
     *
     * @param Http $request
     * @return string
     */
    protected function getPostRole(Http $request): string
    {
        /** @var array $customerParams */
        $customerParams = $request->getParam('customer', []);

        $role = '';
        if (!empty($customerParams[ResourceTypes::VALUE_ROLE])) {
            if (is_string($customerParams[ResourceTypes::VALUE_ROLE])) {
                $role = $customerParams[ResourceTypes::VALUE_ROLE];
            }
        }

        return $role;
    }

    /**
     * Get customer permissions from request.
     *
     * @param Http $request
     * @return array
     */
    protected function getPostPermissions(Http $request): array
    {
        /** @var array $customerParams */
        $customerParams = $request->getParam('customer', []);

        $permissions = [];
        if (!empty($customerParams[ResourceTypes::VALUE_PERMISSION])) {
            if (is_array($customerParams[ResourceTypes::VALUE_PERMISSION])) {
                $permissions = $customerParams[ResourceTypes::VALUE_PERMISSION];
            }
        }

        return $permissions;
    }
}
