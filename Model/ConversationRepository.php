<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MaxStan\LiveChat\Api\ConversationRepositoryInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterface;
use MaxStan\LiveChat\Api\Data\ConversationSearchResultsInterface;
use MaxStan\LiveChat\Api\Data\ConversationSearchResultsInterfaceFactory;
use MaxStan\LiveChat\Model\ResourceModel\Conversation as ConversationResource;
use MaxStan\LiveChat\Model\ResourceModel\Conversation\CollectionFactory;

readonly class ConversationRepository implements ConversationRepositoryInterface
{
    public function __construct(
        private ConversationResource $resource,
        private ConversationFactory $conversationFactory,
        private CollectionFactory $collectionFactory,
        private ConversationSearchResultsInterfaceFactory $searchResultsFactory,
        private CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getById(int $conversationId): ConversationInterface
    {
        $conversation = $this->conversationFactory->create();
        $this->resource->load($conversation, $conversationId);

        if (!$conversation->getId()) {
            throw new NoSuchEntityException(
                __('The conversation with id "%1" does not exist.', $conversationId)
            );
        }

        return $conversation;
    }

    /**
     * @inheritDoc
     */
    public function save(ConversationInterface $conversation): ConversationInterface
    {
        try {
            $this->resource->save($conversation);
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the conversation: %1', $e->getMessage()),
                $e
            );
        }

        return $conversation;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ConversationSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(ConversationInterface $conversation): bool
    {
        try {
            $this->resource->delete($conversation);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete the conversation: %1', $e->getMessage()),
                $e
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $conversationId): bool
    {
        $conversation = $this->getById($conversationId);

        return $this->delete($conversation);
    }
}
