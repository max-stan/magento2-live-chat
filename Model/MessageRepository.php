<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model;

use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Api\Data\MessageSearchResultsInterface;
use MaxStan\LiveChat\Api\Data\MessageSearchResultsInterfaceFactory;
use MaxStan\LiveChat\Api\MessageRepositoryInterface;
use MaxStan\LiveChat\Model\ResourceModel\Message as MessageResource;
use MaxStan\LiveChat\Model\ResourceModel\Message\CollectionFactory;

readonly class MessageRepository implements MessageRepositoryInterface
{
    public function __construct(
        private MessageResource $resource,
        private MessageFactory $messageFactory,
        private CollectionFactory $collectionFactory,
        private MessageSearchResultsInterfaceFactory $searchResultsFactory,
        private CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getById(int $messageId): MessageInterface
    {
        $message = $this->messageFactory->create();
        $this->resource->load($message, $messageId);

        if (!$message->getId()) {
            throw new NoSuchEntityException(
                __('The message with id "%1" does not exist.', $messageId)
            );
        }

        return $message;
    }

    /**
     * @inheritDoc
     */
    public function save(MessageInterface $message): MessageInterface
    {
        try {
            $this->resource->save($message);
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the message: %1', $e->getMessage()),
                $e
            );
        }

        return $message;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): MessageSearchResultsInterface
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
    public function delete(MessageInterface $message): bool
    {
        try {
            $this->resource->delete($message);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete the message: %1', $e->getMessage()),
                $e
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $messageId): bool
    {
        $message = $this->getById($messageId);

        return $this->delete($message);
    }
}
