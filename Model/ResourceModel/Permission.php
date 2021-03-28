<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource model for customer permissions.
 */
class Permission extends AbstractDb
{
    /**
     * Permission table name.
     */
    const TABLE_NAME = 'dized_front_acl_permissions';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
}
