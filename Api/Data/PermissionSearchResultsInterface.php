<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Permission Search Results Interface.
 */
interface PermissionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return PermissionInterface[]
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param PermissionInterface[] $items
     * @return PermissionSearchResultsInterface
     */
    public function setItems(array $items);
}
