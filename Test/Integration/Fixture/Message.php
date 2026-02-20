<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Test\Integration\Fixture;

use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use MaxStan\LiveChat\Api\Data\MessageInterfaceFactory;
use MaxStan\LiveChat\Api\MessageRepositoryInterface;

class Message implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'conversation_id' => null,
        'sender_id' => null,
        'text' => 'Test message',
        'status' => 0,
        'created_at' => null,
    ];

    public function __construct(
        private readonly MessageInterfaceFactory $messageFactory,
        private readonly MessageRepositoryInterface $messageRepository
    ) {
    }

    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        if (isset($data['conversation']) && $data['conversation'] instanceof DataObject) {
            $data['conversation_id'] = (int)$data['conversation']->getId();
            unset($data['conversation']);
        }

        if (isset($data['sender']) && $data['sender'] instanceof DataObject) {
            $data['sender_id'] = (int)$data['sender']->getId();
            unset($data['sender']);
        }

        $message = $this->messageFactory->create();
        $message->setConversationId($data['conversation_id']);
        $message->setSenderId($data['sender_id']);
        $message->setText($data['text']);
        $message->setStatus($data['status']);

        if ($data['created_at']) {
            $message->setCreatedAt($data['created_at']);
        } else {
            $message->setCreatedAt(date('Y-m-d H:i:s'));
        }

        $this->messageRepository->save($message);

        return new DataObject([
            'id' => $message->getId(),
            'conversation_id' => $message->getConversationId(),
            'sender_id' => $message->getSenderId(),
            'text' => $message->getText(),
            'status' => $message->getStatus(),
            'created_at' => $message->getCreatedAt(),
        ]);
    }

    public function revert(DataObject $data): void
    {
        $messageId = (int)$data->getId();
        if ($messageId) {
            try {
                $this->messageRepository->deleteById($messageId);
            } catch (\Exception) {
                // already deleted
            }
        }
    }
}
