<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model\ResourceModel\Permission;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Customer permission collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(
            \DiZed\FrontAcl\Model\Permission::class,
            \DiZed\FrontAcl\Model\ResourceModel\Permission::class
        );
    }

    /**
     * Join customer to the result.
     *
     * @param string $alias
     * @return Collection
     */
    public function joinCustomer(string $alias = 'customer'): Collection
    {
        $this->getSelect()->joinLeft(
            [$alias => $this->getTable('customer_entity')],
            "main_table.customer_id = {$alias}.entity_id",
            $this->getCustomerColumns($alias)
        );

        return $this;
    }

    /**
     * Get customer columns.
     *
     * @param string $alias
     * @return string[]
     */
    public function getCustomerColumns(string $alias = 'customer'): array
    {
        return [
            "email AS {$alias}_email",
            "firstname AS {$alias}_firstname",
            "lastname AS {$alias}_lastname",
        ];
    }
}
