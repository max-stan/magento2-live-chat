<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model;

use DateTime;
use DateTimeZone;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Model\ResourceModel\User as AdminUserResource;
use Magento\User\Model\UserFactory as AdminUserFactory;
use MaxStan\LiveChat\Api\ConversationRepositoryInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterface;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterfaceFactory;
use MaxStan\LiveChat\Api\Data\MessageInterfaceFactory;
use MaxStan\LiveChat\Api\MessageRepositoryInterface;
use MaxStan\LiveChat\Api\UserChatManagementInterface;
use MaxStan\LiveChat\Model\Topic\ConversationMessageResolver;
use MaxStan\Mercure\Api\MercureHubInterface;
use MaxStan\Mercure\Service\MercureTopicPublisher;

readonly class UserChatManagement implements UserChatManagementInterface
{
    public const int MESSAGES_LIMIT = 50;
    public const int CONVERSATIONS_LIMIT = 10;

    public function __construct(
        private UserContextInterface $userContext,
        private ConversationRepositoryInterface $conversationRepository,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private MessageRepositoryInterface $messageRepository,
        private SortOrderBuilder $sortOrderBuilder,
        private ConversationInterfaceFactory $conversationFactory,
        private MessageInterfaceFactory $messageFactory,
        private CustomerRepositoryInterface $customerRepository,
        private AdminUserFactory $adminUserFactory,
        private AdminUserResource $adminUserResource,
        private MercureTopicPublisher $mercureTopicPublisher,
        private MercureHubInterface $mercureHub
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(int $conversationId, string $text): Message
    {
        $this->checkAuthorization($conversationId);
        $createdAt = new DateTime('', new DateTimeZone('UTC'));

        $userId = $this->getUserId();
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
        $this->mercureTopicPublisher->execute(
            ConversationMessageResolver::IRI . "_$conversationId",
            ['type' => 'message:received', 'data' => $message->getData()]
        );

        return $message;
    }

    /**
     * @inheritDoc
     */
    public function getMessages(int $conversationId, int $currentPage = 0): array
    {
        $conversation = $this->checkAuthorization($conversationId);

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
    public function getConversations(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ConversationInterface::USER_ID, $this->getUserId())
            ->create();
        $searchCriteria
            ->setPageSize(static::CONVERSATIONS_LIMIT)
            ->setCurrentPage(1);
        $conversations = $this->conversationRepository->getList($searchCriteria)
            ->getItems();

        foreach ($conversations as $conversation) {
            $conversation->setData(
                'messages',
                $this->getMessages((int)$conversation->getId())
            );
        }

        $this->mercureHub->setAuthorizationHeader();

        return $conversations;
    }

    /**
     * @inheritDoc
     */
    public function createConversation(): Conversation
    {
        $customerId = $this->getUserId();

        if (!$customerId) {
            throw new AuthorizationException(
                __('You are not authorized for requested resource.')
            );
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ConversationInterface::USER_ID, $customerId)
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
        $conversation->setUserId($customerId)
            ->setCreatedAt($createdAt->format('Y-m-d H:i:s'));

        try {
            $this->conversationRepository->save($conversation);
        } catch (CouldNotSaveException) {
            throw new LocalizedException(
                __('Something went wrong while creating conversation.')
            );
        }

        $conversation->setData('messages', []);
        $this->mercureHub->setAuthorizationHeader();

        return $conversation;
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

    /**
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     */
    private function checkAuthorization(int $conversationId): ConversationInterface
    {
        $customerId = $this->getUserId();
        $conversation = $this->conversationRepository->getById($conversationId);
        $userType = $this->userContext->getUserType();
        if (
            $userType === UserContextInterface::USER_TYPE_CUSTOMER
            && $customerId
            && $conversation->getUserId() === $customerId
        ) {
            return $conversation;
        }

        if ($userType === UserContextInterface::USER_TYPE_ADMIN) {
            return $conversation;
        }

        throw new AuthorizationException(
            __('You are not authorized for requested resource.')
        );
    }

    private function getUserId(): int
    {
        return (int)$this->userContext->getUserId();
    }
}
