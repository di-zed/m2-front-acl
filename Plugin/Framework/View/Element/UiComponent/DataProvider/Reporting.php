<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Plugin\Framework\View\Element\UiComponent\DataProvider;

use DiZed\FrontAcl\Helper\Data;
use DiZed\FrontAcl\Model\Config\Source\ResourceTypes;
use DiZed\FrontAcl\Model\ResourceModel\Permission;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting as UiReporting;

/**
 * Plugin for the data provider reporting.
 *
 * @see \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting
 */
class Reporting
{
    /**
     * Tables with customers.
     */
    const CUSTOMER_TABLE_NAMES = ['customer_entity', 'customer_grid_flat'];

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
     * Adding filter by customer role.
     *
     * @param UiReporting $reporting
     * @param AbstractCollection $collection
     * @return AbstractCollection
     * @see \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting::search
     */
    public function afterSearch(
        UiReporting $reporting,
        AbstractCollection $collection
    ): AbstractCollection {
        if ($this->helper->isModuleEnabled()) {
            if (in_array($collection->getMainTable(), self::CUSTOMER_TABLE_NAMES)) {
                $collection->getSelect()
                    ->joinLeft(
                        ['cp' => $collection->getConnection()->getTableName(Permission::TABLE_NAME)],
                        'cp.customer_id = main_table.entity_id
                            AND cp.resource_type = "' . ResourceTypes::VALUE_ROLE . '"',
                        ['resource_id' => 'cp.resource_id']
                    );
            }
        }

        return $collection;
    }
}
