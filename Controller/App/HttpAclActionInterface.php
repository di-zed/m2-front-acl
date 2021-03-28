<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Controller\App;

use Magento\Framework\App\ActionInterface;

/**
 * Marker for actions processing ACL rules.
 *
 * @see \DiZed\FrontAcl\Plugin\Controller\App\HttpAclActionInterface
 */
interface HttpAclActionInterface extends ActionInterface
{

}
