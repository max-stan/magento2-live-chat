<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model\Topic;

use MaxStan\LiveChat\Api\Data\ConversationInterface;
use MaxStan\LiveChat\Model\ResourceModel\Conversation\Collection;
use MaxStan\LiveChat\Model\ResourceModel\Conversation\CollectionFactory;
use MaxStan\Mercure\Api\TopicsResolverInterface;

readonly class ConversationMessageResolver implements TopicsResolverInterface
{
    public const string IRI = 'conversation_index_index';

    public function __construct(
        private CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getTopics(?int $customerId): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $topics = [];
        foreach ($collection->getColumnValues(ConversationInterface::ID) as $id) {
            $topics[] = self::IRI . "_$id";
        }

        return $topics;
    }
}
