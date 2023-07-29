<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\FrontAcl\Model;

use DiZed\FrontAcl\Api\Data\PermissionInterface;
use DiZed\FrontAcl\Api\Data\PermissionSearchResultsInterface;
use DiZed\FrontAcl\Api\Data\PermissionSearchResultsInterfaceFactory;
use DiZed\FrontAcl\Api\PermissionRepositoryInterface;
use DiZed\FrontAcl\Model\Config\Source\ResourceTypes;
use DiZed\FrontAcl\Model\ResourceModel\Permission\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Permission repository.
 */
class PermissionRepository implements PermissionRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var PermissionSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Repository constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PermissionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PermissionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionProcessor = $collectionProcessor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): PermissionInterface
    {
        $collection = $this->collectionFactory->create();

        $collection->getResource()->load($collection, $id);
        if (!$collection->getId()) {
            throw new NoSuchEntityException(__('Unable to find customer permission with ID %1.', $id));
        }

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function save(PermissionInterface $permission): PermissionInterface
    {
        $permission->getResource()->save($permission);

        return $permission;
    }

    /**
     * @inheritdoc
     */
    public function delete(PermissionInterface $permission): void
    {
        $permission->getResource()->delete($permission);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PermissionSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        // add customers data:
        $collection->joinCustomer();

        // add filters to collection:
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }

        // add sort orders to collection:
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $direction = ($sortOrder->getDirection() == SortOrder::SORT_DESC)
                ? SortOrder::SORT_DESC
                : SortOrder::SORT_ASC;
            $collection->addOrder($sortOrder->getField(), $direction);
        }

        // add paging to collection:
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getRoleItems(int $customerId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(PermissionInterface::FIELD_CUSTOMER_ID, $customerId, 'eq')
            ->addFilter(PermissionInterface::FIELD_RESOURCE_TYPE, ResourceTypes::VALUE_ROLE, 'eq')
            ->create();

        /** @var PermissionSearchResults $result */
        $result = $this->getList($searchCriteria);

        return $result->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getPermissionItems(int $customerId): array
    {
        $this->searchCriteriaBuilder
            ->addFilter(PermissionInterface::FIELD_CUSTOMER_ID, $customerId, 'eq')
            ->addFilter(PermissionInterface::FIELD_RESOURCE_TYPE, ResourceTypes::VALUE_PERMISSION, 'eq');
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /** @var PermissionSearchResults $result */
        $result = $this->getList($searchCriteria);

        return $result->getItems();
    }
}
