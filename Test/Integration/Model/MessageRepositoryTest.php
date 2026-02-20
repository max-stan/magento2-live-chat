<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Test\Integration\Model;

use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Api\Data\MessageInterfaceFactory;
use MaxStan\LiveChat\Api\MessageRepositoryInterface;
use MaxStan\LiveChat\Test\Integration\Fixture\Conversation as ConversationFixture;
use MaxStan\LiveChat\Test\Integration\Fixture\Message as MessageFixture;
use PHPUnit\Framework\TestCase;

class MessageRepositoryTest extends TestCase
{
    private ?MessageRepositoryInterface $repository;
    private ?SearchCriteriaBuilder $searchCriteriaBuilder;
    private ?SortOrderBuilder $sortOrderBuilder;
    private ?MessageInterfaceFactory $messageFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->repository = $objectManager->get(MessageRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->sortOrderBuilder = $objectManager->get(SortOrderBuilder::class);
        $this->messageFactory = $objectManager->get(MessageInterfaceFactory::class);
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
        DataFixture(MessageFixture::class, [
            'conversation' => '$conversation$',
            'sender' => '$customer$',
            'text' => 'Hello world',
            'status' => 1,
        ], 'message'),
    ]
    public function testGetById(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $fixtureMessage = $fixtures->get('message');
        $fixtureConversation = $fixtures->get('conversation');
        $fixtureCustomer = $fixtures->get('customer');

        $message = $this->repository->getById((int)$fixtureMessage->getId());

        $this->assertEquals($fixtureConversation->getId(), $message->getConversationId());
        $this->assertEquals($fixtureCustomer->getId(), $message->getSenderId());
        $this->assertEquals('Hello world', $message->getText());
        $this->assertEquals(1, $message->getStatus());
    }

    public function testGetByIdThrowsForNonExistent(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById(99999);
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    #[
        DbIsolation(true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
    ]
    public function testSave(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $conversationId = (int)$fixtures->get('conversation')->getId();
        $customerId = (int)$fixtures->get('customer')->getId();

        $message = $this->messageFactory->create();
        $message->setConversationId($conversationId);
        $message->setSenderId($customerId);
        $message->setText('Test save message');
        $message->setStatus(0);
        $message->setCreatedAt('2026-01-01 12:00:00');

        $saved = $this->repository->save($message);
        $this->assertNotNull($saved->getId());

        $loaded = $this->repository->getById((int)$saved->getId());
        $this->assertEquals($conversationId, $loaded->getConversationId());
        $this->assertEquals($customerId, $loaded->getSenderId());
        $this->assertEquals('Test save message', $loaded->getText());
        $this->assertEquals(0, $loaded->getStatus());
        $this->assertEquals('2026-01-01 12:00:00', $loaded->getCreatedAt());
    }

    /**
     * @throws LocalizedException
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'text' => 'Msg 1'], 'm1'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'text' => 'Msg 2'], 'm2'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'text' => 'Msg 3'], 'm3'),
    ]
    public function testGetListByConversation(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $conversationId = (int)$fixtures->get('conversation')->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(MessageInterface::CONVERSATION_ID, $conversationId)
            ->create();

        $results = $this->repository->getList($searchCriteria);

        $this->assertEquals(3, $results->getTotalCount());
    }

    /**
     * @throws LocalizedException
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'status' => 0], 'm1'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'status' => 1], 'm2'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'status' => 0], 'm3'),
    ]
    public function testGetListFilterByStatus(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $conversationId = (int)$fixtures->get('conversation')->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(MessageInterface::CONVERSATION_ID, $conversationId)
            ->addFilter(MessageInterface::STATUS, 0)
            ->create();

        $results = $this->repository->getList($searchCriteria);

        $this->assertEquals(2, $results->getTotalCount());
    }

    /**
     * @throws LocalizedException
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'text' => 'First', 'created_at' => '2026-01-01 10:00:00'], 'm1'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'text' => 'Second', 'created_at' => '2026-01-01 11:00:00'], 'm2'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$', 'text' => 'Third', 'created_at' => '2026-01-01 12:00:00'], 'm3'),
    ]
    public function testGetListWithSortOrder(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $conversationId = (int)$fixtures->get('conversation')->getId();

        $sortOrder = $this->sortOrderBuilder
            ->setField(MessageInterface::CREATED_AT)
            ->setDescendingDirection()
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(MessageInterface::CONVERSATION_ID, $conversationId)
            ->addSortOrder($sortOrder)
            ->create();

        $results = $this->repository->getList($searchCriteria);
        $items = array_values($results->getItems());

        $this->assertCount(3, $items);
        $this->assertEquals('Third', $items[0]->getText());
        $this->assertEquals('Second', $items[1]->getText());
        $this->assertEquals('First', $items[2]->getText());
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    #[
        DbIsolation(true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$'], 'message'),
    ]
    public function testDelete(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $messageId = (int)$fixtures->get('message')->getId();

        $message = $this->repository->getById($messageId);
        $this->repository->delete($message);

        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($messageId);
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    #[
        DbIsolation(true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
        DataFixture(MessageFixture::class, ['conversation' => '$conversation$', 'sender' => '$customer$'], 'message'),
    ]
    public function testDeleteById(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $messageId = (int)$fixtures->get('message')->getId();

        $this->repository->deleteById($messageId);

        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($messageId);
    }
}
