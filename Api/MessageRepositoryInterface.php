<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Api\Data\MessageSearchResultsInterface;

interface MessageRepositoryInterface
{
    /**
     * @param int $messageId
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $messageId): MessageInterface;

    /**
     * @param \MaxStan\LiveChat\Api\Data\MessageInterface $message
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     * @throws CouldNotSaveException
     */
    public function save(MessageInterface $message): MessageInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MaxStan\LiveChat\Api\Data\MessageSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): MessageSearchResultsInterface;

    /**
     * @param \MaxStan\LiveChat\Api\Data\MessageInterface $message
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(MessageInterface $message): bool;

    /**
     * @param int $messageId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $messageId): bool;
}
