<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Block\Adminhtml\Edit\Tab;

use DiZed\FrontAcl\Api\RoleManagementInterface;
use DiZed\FrontAcl\Helper\Data as FrontAclHelper;
use DiZed\FrontAcl\Model\Config\Source\Permissions as SourcePermissions;
use DiZed\FrontAcl\Model\Config\Source\ResourceTypes;
use DiZed\FrontAcl\Model\Config\Source\Roles as SourceRoles;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Template;
use Magento\Integration\Helper\Data as IntegrationHelper;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Permissions Tab block.
 */
class Permissions extends Template implements TabInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var IntegrationHelper
     */
    protected $helperIntegration;

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
    protected $roleManagement;

    /**
     * @var FrontAclHelper
     */
    protected $helper;

    /**
     * Block constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Json $serializer
     * @param Session $session
     * @param IntegrationHelper $helperIntegration
     * @param SourceRoles $sourceRoles
     * @param SourcePermissions $sourcePermissions
     * @param RoleManagementInterface $roleManagement
     * @param FrontAclHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Json $serializer,
        Session $session,
        IntegrationHelper $helperIntegration,
        SourceRoles $sourceRoles,
        SourcePermissions $sourcePermissions,
        RoleManagementInterface $roleManagement,
        FrontAclHelper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->registry = $registry;
        $this->serializer = $serializer;
        $this->session = $session;
        $this->helperIntegration = $helperIntegration;
        $this->sourceRoles = $sourceRoles;
        $this->sourcePermissions = $sourcePermissions;
        $this->roleManagement = $roleManagement;
        $this->helper = $helper;
    }

    /**
     * Get customer ID.
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int)$this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get customer.
     *
     * @return CustomerInterface
     */
    public function getCustomer(): ?CustomerInterface
    {
        if (!$this->hasData('customer')) {
            if ($customerId = $this->getCustomerId()) {
                $customer = $this->roleManagement->getCustomerData($customerId);
                if ($customer && $customer->getId()) {
                    $this->setData('customer', $customer);
                }
            }
        }

        return $this->getData('customer');
    }

    /**
     * Get customer form data.
     *
     * @return array
     */
    public function getCustomerFormData(): array
    {
        $result = [];

        if ($customerFormData = $this->session->getFrontAclCustomerFormData()) {
            if (is_array($customerFormData)) {
                $result = $customerFormData;
            }
        }

        return $result;
    }

    /**
     * Remove customer form data.
     *
     * @return bool
     */
    public function removeCustomerFormData(): bool
    {
        if ($customerFormData = $this->session->getFrontAclCustomerFormData()) {
            $this->session->unsFrontAclCustomerFormData();
        }

        return true;
    }

    /**
     * Get tab label.
     *
     * @return Phrase
     */
    public function getTabLabel(): Phrase
    {
        return __('Front ACL');
    }

    /**
     * Get tab title.
     *
     * @return Phrase
     */
    public function getTabTitle(): Phrase
    {
        return __('Front ACL');
    }

    /**
     * Can show tab?
     *
     * @return bool
     */
    public function canShowTab(): bool
    {
        if (!$this->helper->isModuleEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Is hidden?
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        if (!$this->helper->isModuleEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * Get tab class.
     *
     * @return string
     */
    public function getTabClass(): string
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl(): string
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded(): bool
    {
        return false;
    }

    /**
     * Return html select input element for roles.
     *
     * @return string
     */
    public function getRoleHtmlSelect(): string
    {
        $customer = $this->getCustomer();
        $customerFormData = $this->getCustomerFormData();

        // get default role value:
        $roleValue = '';
        if (array_key_exists(ResourceTypes::VALUE_ROLE, $customerFormData)) {
            $roleValue = $customerFormData[ResourceTypes::VALUE_ROLE];
        } elseif ($customer) {
            $roleValue = $this->roleManagement->getRole($customer);
        }

        try {
            $select = $this->getLayout()->createBlock(Select::class)
                ->setName('customer[' . ResourceTypes::VALUE_ROLE . ']')
                ->setId(ResourceTypes::VALUE_ROLE)
                ->setClass(ResourceTypes::VALUE_ROLE)
                ->setOptions($this->sourceRoles->getAllOptions())
                ->setValue($roleValue)
                ->setExtraParams('data-form-part="customer_form"');
        } catch (\Exception $e) {
            return '';
        }

        return $select->getHtml();
    }

    /**
     * Get json representation of permission resource tree.
     *
     * @return array
     */
    public function getTreePermissions(): array
    {
        $permissionResources = $this->sourcePermissions->getAclResources();
        foreach ($permissionResources as $key => $permissionResource) {
            $permissionResources[$key] = $this->prepareTreePermissionItem($permissionResource);
        }

        return $this->helperIntegration->mapResources($permissionResources);
    }

    /**
     * Get selected permission resources for tree.
     *
     * @return array
     */
    public function getTreeSelectedPermissions(): array
    {
        $result = [];

        $customer = $this->getCustomer();
        $customerFormData = $this->getCustomerFormData();

        if (array_key_exists(ResourceTypes::VALUE_PERMISSION, $customerFormData)) {
            $formData = $customerFormData[ResourceTypes::VALUE_PERMISSION];
            if (is_array($formData)) {
                foreach ($this->sourcePermissions->toOptionArray() as $permission) {
                    if (!empty($formData[$permission['value']])) {
                        $result[] = $permission['value'];
                    }
                }
            }
        } elseif ($customer) {
            $result = $this->roleManagement->getPermissions($customer);
        }

        return $result;
    }

    /**
     * Get config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'nameRole' => ResourceTypes::VALUE_ROLE,
            'namePermission' => ResourceTypes::VALUE_PERMISSION,
            'initData' => $this->getTreePermissions(),
            'selectedData' => $this->getTreeSelectedPermissions(),
            'defaultPermissions' => $this->roleManagement->getDefaultPermissions(),
        ];
    }

    /**
     * Get JSON config.
     *
     * @return string
     */
    public function getJsonConfig(): string
    {
        return $this->serializer->serialize($this->getConfig());
    }

    /**
     * Preparing item for permission tree.
     *
     * @param array $permissionResource
     * @return array
     */
    protected function prepareTreePermissionItem(array $permissionResource): array
    {
        if (!empty($permissionResource['children']) && is_array($permissionResource['children'])) {
            foreach ($permissionResource['children'] as $key => $childResource) {
                $permissionResource['children'][$key] = $this->prepareTreePermissionItem($childResource);
            }
        }

        /*
         * if need some changes for resource item, for example:
         * $permissionResource['id'] = ...;
         */

        return $permissionResource;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml(): string
    {
        if ($this->canShowTab()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    protected function _afterToHtml($html): string
    {
        $this->removeCustomerFormData();

        return parent::_afterToHtml($html);
    }
}
