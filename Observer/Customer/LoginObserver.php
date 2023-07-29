<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Observer\Customer;

use DiZed\FrontAcl\Api\RoleManagementInterface;
use DiZed\FrontAcl\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for "customer_login" event.
 * Adding permission to the customer session.
 *
 * @see \Magento\Customer\Model\Session::setCustomerAsLoggedIn
 */
class LoginObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

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
     * @param Session $customerSession
     * @param RoleManagementInterface $roleManagement
     * @param Data $helper
     */
    public function __construct(
        Session $customerSession,
        RoleManagementInterface $roleManagement,
        Data $helper
    ) {
        $this->customerSession = $customerSession;
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

        /** @var Customer $customer */
        $customer = $observer->getCustomer();

        // set role:
        if ($role = $this->roleManagement->getRole($customer)) {
            $this->customerSession->setRole($role);
        }

        // set permissions:
        if ($permissions = $this->roleManagement->getPermissions($customer)) {
            $this->customerSession->setPermissions($permissions);
        }
    }
}
