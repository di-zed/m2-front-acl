<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Ui\Component\Listing\Column;

use DiZed\FrontAcl\Api\Data\PermissionInterface;
use DiZed\FrontAcl\Api\RoleManagementInterface;
use DiZed\FrontAcl\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * New column for customer grid.
 */
class ResourceId extends Column
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
     * Column constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param RoleManagementInterface $roleManagement
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        RoleManagementInterface $roleManagement,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->roleManagement = $roleManagement;
        $this->helper = $helper;
    }

    /**
     * Add new field value.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if ($this->helper->isModuleEnabled()) {
            if (!empty($dataSource['data']['items']) && is_array($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as &$item) {
                    $roleName = $this->roleManagement->getRoleName($item['entity_id']);
                    $item[PermissionInterface::FIELD_RESOURCE_ID] = $roleName;
                }
            }
        }

        return $dataSource;
    }
}
