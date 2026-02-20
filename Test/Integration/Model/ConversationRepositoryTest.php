<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Test\Integration\Model;

use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\LiveChat\Api\ConversationRepositoryInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterfaceFactory;
use MaxStan\LiveChat\Test\Integration\Fixture\Conversation as ConversationFixture;
use PHPUnit\Framework\TestCase;

class ConversationRepositoryTest extends TestCase
{
    private ?ConversationRepositoryInterface $repository;
    private ?SearchCriteriaBuilder $searchCriteriaBuilder;
    private ?ConversationInterfaceFactory $conversationFactory;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->repository = $objectManager->get(ConversationRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->conversationFactory = $objectManager->get(ConversationInterfaceFactory::class);
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
    ]
    public function testGetById(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $fixtureConversation = $fixtures->get('conversation');
        $fixtureCustomer = $fixtures->get('customer');

        $conversation = $this->repository->getById((int)$fixtureConversation->getId());

        $this->assertEquals($fixtureCustomer->getId(), $conversation->getUserId());
        $this->assertNotEmpty($conversation->getCreatedAt());
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
    ]
    public function testSave(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $fixtureCustomer = $fixtures->get('customer');
        $conversation = $this->conversationFactory->create();
        $userId = (int)$fixtureCustomer->getId();
        $conversation->setUserId($userId);
        $conversation->setCreatedAt('2026-01-01 00:00:00');

        $saved = $this->repository->save($conversation);

        $this->assertNotNull($saved->getId());

        $loaded = $this->repository->getById((int)$saved->getId());
        $this->assertEquals($userId, $loaded->getUserId());
        $this->assertEquals('2026-01-01 00:00:00', $loaded->getCreatedAt());
    }

    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation1'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation2'),
    ]
    public function testGetList(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $customer = $fixtures->get('customer');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ConversationInterface::USER_ID, (int)$customer->getId())
            ->create();

        $results = $this->repository->getList($searchCriteria);

        $this->assertGreaterThanOrEqual(2, $results->getTotalCount());
    }

    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'c1'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'c2'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'c3'),
    ]
    public function testGetListWithPagination(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $customer = $fixtures->get('customer');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ConversationInterface::USER_ID, (int)$customer->getId())
            ->create();
        $searchCriteria->setPageSize(2)->setCurrentPage(1);

        $results = $this->repository->getList($searchCriteria);

        $this->assertCount(2, $results->getItems());
        $this->assertGreaterThanOrEqual(3, $results->getTotalCount());
    }

    #[
        DbIsolation(true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
    ]
    public function testDelete(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $conversationId = (int)$fixtures->get('conversation')->getId();

        $conversation = $this->repository->getById($conversationId);
        $this->repository->delete($conversation);

        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($conversationId);
    }

    #[
        DbIsolation(true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(ConversationFixture::class, ['customer' => '$customer$'], 'conversation'),
    ]
    public function testDeleteById(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $conversationId = (int)$fixtures->get('conversation')->getId();

        $this->repository->deleteById($conversationId);

        $this->expectException(NoSuchEntityException::class);
        $this->repository->getById($conversationId);
    }
}
