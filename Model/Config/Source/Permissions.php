<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model\Config\Source;

/**
 * Source for the customer permissions.
 */
class Permissions extends Roles
{
    /**
     * @var string
     */
    public static $resourceId = 'FrontAcl_Permission::index';
}
