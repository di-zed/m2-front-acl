<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Api;

use DiZed\FrontAcl\Api\Data\PermissionInterface;
use DiZed\FrontAcl\Api\Data\PermissionSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Permission Repository Interface.
 */
interface PermissionRepositoryInterface
{
    /**
     * Get by ID.
     *
     * @param int $id
     * @return PermissionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): PermissionInterface;

    /**
     * Save permission for a customer.
     *
     * @param PermissionInterface $permission
     * @return PermissionInterface
     * @throws AlreadyExistsException
     */
    public function save(PermissionInterface $permission): PermissionInterface;

    /**
     * Remove permission for a customer.
     *
     * @param PermissionInterface $permission
     * @return void
     * @throws \Exception
     */
    public function delete(PermissionInterface $permission): void;

    /**
     * Get permission search results.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return PermissionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PermissionSearchResultsInterface;

    /**
     * Get role items.
     *
     * @param int $customerId
     * @return PermissionInterface[]
     */
    public function getRoleItems(int $customerId): array;

    /**
     * Get permission items.
     *
     * @param int $customerId
     * @return PermissionInterface[]
     */
    public function getPermissionItems(int $customerId): array;
}
