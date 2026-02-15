<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MaxStan\LiveChat\Api\Data\ConversationInterface;
use MaxStan\LiveChat\Api\Data\ConversationSearchResultsInterface;

interface ConversationRepositoryInterface
{
    /**
     * @param int $conversationId
     * @return \MaxStan\LiveChat\Api\Data\ConversationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $conversationId): ConversationInterface;

    /**
     * @param \MaxStan\LiveChat\Api\Data\ConversationInterface $conversation
     * @return \MaxStan\LiveChat\Api\Data\ConversationInterface
     * @throws CouldNotSaveException
     */
    public function save(ConversationInterface $conversation): ConversationInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MaxStan\LiveChat\Api\Data\ConversationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ConversationSearchResultsInterface;

    /**
     * @param \MaxStan\LiveChat\Api\Data\ConversationInterface $conversation
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ConversationInterface $conversation): bool;

    /**
     * @param int $conversationId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $conversationId): bool;
}
