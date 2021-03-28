<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model\Config\Source;

/**
 * Source for the customer default relations.
 */
class DefaultRelations extends Permissions
{
    /**
     * @var string
     */
    public static $resourceId = 'FrontAcl_Defaults::index';

    /**
     * Get real role ID.
     *
     * @param string $resourceId
     * @return string
     */
    public function getRealRoleId(string $resourceId): string
    {
        $rolePart = substr($resourceId, 0, strpos($resourceId, '::'));

        $roleName = strtolower(
            preg_replace(
                '/(?<!^)[A-Z]/',
                '_$0',
                substr($rolePart, strpos($rolePart, '_') + 1)
            )
        );

        return substr(Roles::$resourceId, 0, strpos(Roles::$resourceId, '::')) . '::' . $roleName;
    }

    /**
     * Get real permission ID.
     *
     * @param string $resourceId
     * @return string
     */
    public function getRealPermissionId(string $resourceId): string
    {
        $permissionPart = substr($resourceId, strpos($resourceId, '::') + 2);

        return substr(Permissions::$resourceId, 0, strpos(Permissions::$resourceId, '::')) . '::' . $permissionPart;
    }
}
