<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Plugin\Customer\Api;

use DiZed\FrontAcl\Helper\Data;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Plugin for account management interface.
 *
 * @see \Magento\Customer\Api\AccountManagementInterface
 */
class AccountManagement
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Plugin constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Set a role and permissions for a new customer.
     *
     * @param AccountManagementInterface $accountManagement
     * @param CustomerInterface $customer
     * @return CustomerInterface
     * @see \Magento\Customer\Api\AccountManagementInterface::createAccount
     */
    public function afterCreateAccount(
        AccountManagementInterface $accountManagement,
        CustomerInterface $customer
    ): CustomerInterface {
        if (!$this->helper->isModuleEnabled()) {
            return $customer;
        }

        $roleManagement = $this->helper->getRoleManagement();

        // if a customer has already role then do nothing:
        if ($roleManagement->getRole($customer)) {
            return $customer;
        }

        // set the role and permissions:
        if ($role = $this->getDetectedRole($customer)) {
            if (!$roleManagement->setRole($role, $customer)) {
                return $customer;
            }
            $permissions = $roleManagement->getDefaultPermissions($role);
            $roleManagement->setPermissions($permissions, $customer);
        }

        return $customer;
    }

    /**
     * Detect customer role by his properties. Good point for the additional plugin to add custom logic.
     *
     * @param CustomerInterface $customer
     * @return string
     */
    public function getDetectedRole(CustomerInterface $customer): string
    {
        return '';
    }
}
