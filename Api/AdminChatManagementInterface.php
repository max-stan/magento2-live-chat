<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MaxStan\LiveChat\Model\Message;

interface AdminChatManagementInterface
{
    /**
     * @param int $conversationId
     * @param string $text
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendMessage(int $conversationId, string $text): Message;

    /**
     * @param int $conversationId
     * @param int $currentPage
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface[]
     *
     * @throws NoSuchEntityException
     */
    public function getMessages(int $conversationId, int $currentPage = 1): array;
}
