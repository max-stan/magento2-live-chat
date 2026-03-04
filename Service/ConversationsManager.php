<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\Service;

use DateTime;
use DateTimeZone;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use MaxStan\LiveChat\Api\ConversationRepositoryInterface;
use MaxStan\LiveChat\Api\ConversationsManagerInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterfaceFactory;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Api\MessagesManagerInterface;
use MaxStan\LiveChat\Model\Conversation;
use MaxStan\LiveChat\Model\ResourceModel\Message\Collection;
use MaxStan\LiveChat\Model\ResourceModel\Message\CollectionFactory;
use MaxStan\Mercure\Api\MercureHttpManagementInterface;
use MaxStan\Mercure\Api\MercurePublisherInterface;
use MaxStan\Mercure\Model\Iri;

readonly class ConversationsManager implements ConversationsManagerInterface
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private ConversationInterfaceFactory $conversationFactory,
        private UserContextInterface $userContext,
        private MessagesManagerInterface $messagesManager,
        private MercureHttpManagementInterface $mercureHttpManagement,
        private MercurePublisherInterface $mercurePublisher,
        private Iri $iri,
        private CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $userId = $this->userContext->getUserId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ConversationInterface::USER_ID, $userId)
            ->create();
        $searchCriteria
            ->setPageSize(static::CONVERSATIONS_LIMIT)
            ->setCurrentPage(1);
        $conversations = $this->conversationRepository->getList($searchCriteria)
            ->getItems();

        foreach ($conversations as $conversation) {
            $conversationId = (int)$conversation->getId();
            $conversation->setData('messages', $this->messagesManager->get($conversationId))
                ->setData('total_unread', $this->getTotalUnread($conversationId));
        }

        $this->mercureHttpManagement->attach();

        return $conversations;
    }

    /**
     * @inheritDoc
     */
    public function create(): Conversation
    {
        $userId = (int)$this->userContext->getUserId();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ConversationInterface::USER_ID, $userId)
            ->create();
        $count = $this->conversationRepository->getList($searchCriteria)->getTotalCount();

        if ($count >= self::CONVERSATIONS_LIMIT) {
            throw new LocalizedException(
                __('You have reached the maximum of %1 conversations.', self::CONVERSATIONS_LIMIT)
            );
        }

        $createdAt = new DateTime('', new DateTimeZone('UTC'));
        /** @var Conversation $conversation */
        $conversation = $this->conversationFactory->create();
        $conversation->setUserId($userId)
            ->setCreatedAt($createdAt->format('Y-m-d H:i:s'));

        try {
            $this->conversationRepository->save($conversation);
        } catch (CouldNotSaveException) {
            throw new LocalizedException(
                __('Something went wrong while creating conversation.')
            );
        }

        $conversation->setData('messages', []);
        $topic = $this->iri->get("livechat/$userId");
        $this->mercurePublisher->publish($topic, $conversation->getData(), 'conversation:create');
        $this->mercureHttpManagement->attach();

        return $conversation;
    }

    private function getTotalUnread(int $conversationId): int
    {
        $userId = (int)$this->userContext->getUserId();
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(MessageInterface::CONVERSATION_ID, $conversationId)
            ->addFieldToFilter(MessageInterface::STATUS, MessageInterface::STATUS_UNREAD)
            ->addFieldToFilter(MessageInterface::SENDER_ID, ['neq' => $userId]);


        return $collection->getSize();
    }
}
