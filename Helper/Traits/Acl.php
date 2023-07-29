<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Helper\Traits;

use DiZed\FrontAcl\Helper\Data;
use Magento\Framework\App\ObjectManager;

/**
 * Trait for ACL.
 */
trait Acl
{
    /**
     * Checking customer permissions for some classes.
     *
     * @param object $class
     * @return bool
     * @example You can use in your class:
     * - const FRONT_ACL_ROLE = [];
     * - const FRONT_ACL_PERMISSION = [];
     * - public function isFrontClassAllowed() { return true; }
     */
    protected function isClassAllowed(object $class): bool
    {
        try {
            $reflectionClass = new \ReflectionClass($class);
        } catch (\Exception $e) {
            return false;
        }

        /** @var Data $coreHelper */
        $coreHelper = ObjectManager::getInstance()->get(Data::class);
        $roleManagement = $coreHelper->getRoleManagement();

        // check custom ACL method:
        if ($reflectionClass->hasMethod('isFrontClassAllowed')) {
            return (bool)$class->isFrontClassAllowed();
        } elseif (!$roleManagement->isCustomerLoggedIn()) {
            return false;
        }

        $isAllowed = false;

        // check role:
        if ($reflectionClass->hasConstant('FRONT_ACL_ROLE')) {
            if (is_array($class::FRONT_ACL_ROLE)) {
                foreach ($class::FRONT_ACL_ROLE as $role) {
                    if ($roleManagement->hasRole($role)) {
                        $isAllowed = true;
                        break;
                    }
                }
            }
        }

        // check permissions:
        if ($reflectionClass->hasConstant('FRONT_ACL_PERMISSION')) {
            if (is_array($class::FRONT_ACL_PERMISSION)) {
                foreach ($class::FRONT_ACL_PERMISSION as $permission) {
                    if ($roleManagement->hasPermission($permission)) {
                        $isAllowed = true;
                        break;
                    }
                }
            }
        }

        return $isAllowed;
    }
}
