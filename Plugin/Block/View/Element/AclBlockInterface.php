<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Plugin\Block\View\Element;

use DiZed\FrontAcl\Helper\Data;
use DiZed\FrontAcl\Helper\Traits\Acl;

/**
 * Plugin for Acl Block Interface.
 *
 * @see \DiZed\FrontAcl\Block\View\Element\AclBlockInterface
 */
class AclBlockInterface
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
     * @param \DiZed\FrontAcl\Block\View\Element\AclBlockInterface $subject
     * @param callable $proceed
     * @return string
     * @see \Magento\Framework\View\Element\BlockInterface::toHtml
     */
    public function aroundToHtml(
        \DiZed\FrontAcl\Block\View\Element\AclBlockInterface $subject,
        callable $proceed
    ): string {
        if ($this->helper->isModuleEnabled()) {
            if (!$this->isClassAllowed($subject)) {
                return $this->blockIsNotAllowed();
            }
        }

        return $proceed();
    }

    /**
     * Handling for not allowed block.
     *
     * @return string
     */
    public function blockIsNotAllowed(): string
    {
        return '';
    }
}
