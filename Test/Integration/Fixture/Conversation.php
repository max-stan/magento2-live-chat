<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Test\Integration\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Helper\Bootstrap;
use MaxStan\LiveChat\Api\ConversationRepositoryInterface;
use MaxStan\LiveChat\Api\Data\ConversationInterfaceFactory;

class Conversation implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'user_id' => null,
        'created_at' => null,
    ];

    public function __construct(
        private readonly ConversationInterfaceFactory $conversationFactory,
        private readonly ConversationRepositoryInterface $conversationRepository
    ) {
    }

    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        if (isset($data['customer']) && $data['customer'] instanceof DataObject) {
            $data['user_id'] = (int)$data['customer']->getId();
            unset($data['customer']);
        }

        $conversation = $this->conversationFactory->create();
        $conversation->setUserId($data['user_id']);

        if ($data['created_at']) {
            $conversation->setCreatedAt($data['created_at']);
        } else {
            $conversation->setCreatedAt(date('Y-m-d H:i:s'));
        }

        $this->conversationRepository->save($conversation);

        return new DataObject([
            'id' => $conversation->getId(),
            'user_id' => $conversation->getUserId(),
            'created_at' => $conversation->getCreatedAt(),
        ]);
    }

    public function revert(DataObject $data): void
    {
        $conversationId = (int)$data->getId();
        if ($conversationId) {
            try {
                $this->conversationRepository->deleteById($conversationId);
            } catch (\Exception) {
                // already deleted
            }
        }
    }
}
