<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Api;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Api\Data\PublicMessageInterface;
use MaxStan\LiveChat\Model\Message;

interface UserChatManagementInterface
{
    /**
     * @param int $conversationId
     * @param string $text
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     *
     * @throws AuthorizationException
     * @throws LocalizedException
     */
    public function sendMessage(int $conversationId, string $text): Message;

    /**
     * @param int $conversationId
     * @param int $currentPage
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface[]
     *
     * @throws NoSuchEntityException
     * @throws AuthorizationException
     */
    public function getMessages(int $conversationId, int $currentPage = 1): array;

    /**
     * @return \MaxStan\LiveChat\Api\Data\PublicConversationInterface[]
     *
     * @throws NoSuchEntityException
     * @throws AuthorizationException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     */
    public function getConversations(): array;
}
