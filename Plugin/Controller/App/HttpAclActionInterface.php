<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Plugin\Controller\App;

use DiZed\FrontAcl\Helper\Data;
use DiZed\FrontAcl\Helper\Traits\Acl;
use Magento\Framework\Exception\NotFoundException;

/**
 * Plugin for Http Acl Action Interface.
 *
 * @see \DiZed\FrontAcl\Controller\App\HttpAclActionInterface
 */
class HttpAclActionInterface
{
    use Acl;

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
     * Check ACL rules.
     *
     * @param \DiZed\FrontAcl\Controller\App\HttpAclActionInterface $subject
     * @param callable $proceed
     * @return mixed
     * @throws NotFoundException
     * @see \Magento\Framework\App\ActionInterface::execute
     */
    public function aroundExecute(
        \DiZed\FrontAcl\Controller\App\HttpAclActionInterface $subject,
        callable $proceed
    ) {
        if ($this->helper->isModuleEnabled()) {
            if (!$this->isClassAllowed($subject)) {
                return $this->actionIsNotAllowed();
            }
        }

        return $proceed();
    }

    /**
     * Handling for not allowed action.
     *
     * @throws NotFoundException
     */
    public function actionIsNotAllowed()
    {
        throw new NotFoundException(__('Page not found.'));
    }
}
