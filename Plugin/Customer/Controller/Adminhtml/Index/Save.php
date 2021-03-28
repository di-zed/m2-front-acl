<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Plugin\Customer\Controller\Adminhtml\Index;

use DiZed\FrontAcl\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Controller\Adminhtml\Index\Save as CustomerSave;

/**
 * Plugin for the customer saving.
 *
 * @see \Magento\Customer\Controller\Adminhtml\Index\Save
 */
class Save
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Plugin constructor.
     *
     * @param Session $session
     * @param Data $helper
     */
    public function __construct(
        Session $session,
        Data $helper
    ) {
        $this->session = $session;
        $this->helper = $helper;
    }

    /**
     * Save post data to the session.
     *
     * @param CustomerSave $action
     * @param Redirect $resultRedirect
     * @return Redirect
     * @see \Magento\Customer\Controller\Adminhtml\Index\Save::execute
     */
    public function afterExecute(
        CustomerSave $action,
        Redirect $resultRedirect
    ): Redirect {
        if ($this->helper->isModuleEnabled()) {
            if ($customerFormData = $this->session->getCustomerFormData()) {
                if (!empty($customerFormData['customer']) && is_array($customerFormData['customer'])) {
                    $this->session->setFrontAclCustomerFormData($customerFormData['customer']);
                }
            }
        }

        return $resultRedirect;
    }
}
