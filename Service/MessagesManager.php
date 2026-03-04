<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\Service;

use DateTime;
use DateTimeZone;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Model\ResourceModel\User as AdminUserResource;
use Magento\User\Model\UserFactory as AdminUserFactory;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Api\Data\MessageInterfaceFactory;
use MaxStan\LiveChat\Api\MessageRepositoryInterface;
use MaxStan\LiveChat\Api\MessagesManagerInterface;
use MaxStan\LiveChat\Model\Message;
use MaxStan\LiveChat\Model\ResourceModel\Message as MessageResource;
use MaxStan\Mercure\Api\MercurePublisherInterface;
use MaxStan\Mercure\Model\Iri;

readonly class MessagesManager implements MessagesManagerInterface
{
    public function __construct(
        private UserContextInterface $userContext,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private MessageRepositoryInterface $messageRepository,
        private SortOrderBuilder $sortOrderBuilder,
        private MessageInterfaceFactory $messageFactory,
        private MercurePublisherInterface $mercurePublisher,
        private Authorization $authorization,
        private CustomerRepositoryInterface $customerRepository,
        private AdminUserFactory $adminUserFactory,
        private AdminUserResource $adminUserResource,
        private Iri $iri,
        private MessageResource $messageResource
    ) {
    }

    /**
     * @inheritDoc
     */
    public function send(int $conversationId, string $text): Message
    {
        $conversation = $this->authorization->isAllowed($conversationId);
        $createdAt = new DateTime('', new DateTimeZone('UTC'));

        $userId = (int)$this->userContext->getUserId();
        /** @var Message $message */
        $message = $this->messageFactory->create();
        $message->setConversationId($conversationId)
            ->setSenderId($userId)
            ->setText($text)
            ->setCreatedAt($createdAt->format('Y-m-d TH:i:s'));

        try {
            $this->messageRepository->save($message);
        } catch (CouldNotSaveException) {
            throw new LocalizedException(
                __('Something went wrong while sending message.')
            );
        }

        $userName = match ($this->userContext->getUserType()) {
            UserContextInterface::USER_TYPE_ADMIN => $this->getAdminName($userId),
            UserContextInterface::USER_TYPE_CUSTOMER => $this->getCustomerName($userId),
            default => __('Unknown')->render()
        };

        $message->setData('sender_name', $userName)
            ->setData('sender_type', $this->userContext->getUserType());

        $conversationCreatorUserId = $conversation->getUserId();
        $this->mercurePublisher->publish(
            $this->iri->get("livechat/$conversationCreatorUserId"),
            $message->getData(),
            'message:receive'
        );

        return $message;
    }

    /**
     * @inheritDoc
     */
    public function get(int $conversationId, int $currentPage = 0): array
    {
        $conversation = $this->authorization->isAllowed($conversationId);
        $createdAtSortOrder = $this->sortOrderBuilder->setField(MessageInterface::CREATED_AT)
            ->setDescendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(MessageInterface::CONVERSATION_ID, $conversationId)
            ->addSortOrder($createdAtSortOrder)
            ->create()
            ->setCurrentPage($currentPage)
            ->setPageSize(self::MESSAGES_LIMIT);

        /** @var Message[] $messages */
        $messages = array_reverse($this->messageRepository->getList($searchCriteria)->getItems());
        $map = [];
        $userId = $conversation->getUserId();
        $map[$userId] = $this->getCustomerName($userId);

        foreach ($messages as $message) {
            $senderId = $message->getSenderId();
            $message->setData('sender_name', $map[$senderId] ?? (string)__('Admin'));
            $message->setData(
                'sender_type',
                $senderId === $userId
                    ? UserContextInterface::USER_TYPE_CUSTOMER
                    : UserContextInterface::USER_TYPE_ADMIN
            );
        }

        return $messages;
    }

    /**
     * @inheritDoc
     */
    public function markAsRead(int $conversationId, array $messageIds = []): bool
    {
        $conversation = $this->authorization->isAllowed($conversationId);
        $this->messageResource->updateStatusBulk($messageIds, MessageInterface::STATUS_READ);

        $conversationCreatorUserId = $conversation->getUserId();
        $this->mercurePublisher->publish(
            $this->iri->get("livechat/$conversationCreatorUserId"),
            [
                'conversation_id' => $conversationId,
                'message_ids' => $messageIds
            ],
            'message:read'
        );

        return true;
    }

    private function getCustomerName(int $customerId): string
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            return $customer->getFirstname() . ' ' . $customer->getLastname();
        } catch (NoSuchEntityException|LocalizedException) {
            return __('Unknown')->render();
        }
    }

    private function getAdminName(int $adminId): string
    {
        $admin = $this->adminUserFactory->create();
        $this->adminUserResource->load($admin, $adminId);
        if ($admin->getId()) {
            return $admin->getFirstName() . ' ' . $admin->getLastName();
        }

        return __('Unknown')->render();
    }
}
