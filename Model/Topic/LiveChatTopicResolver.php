<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\Model\Topic;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use MaxStan\Mercure\Api\MercureTopicProviderInterface;
use MaxStan\Mercure\Model\Iri;

readonly class LiveChatTopicResolver implements MercureTopicProviderInterface
{
    public function __construct(
        private Iri $iri
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getPrivateTopics(int $userId, int $userType): array
    {
        if ($userType === UserContextInterface::USER_TYPE_ADMIN) {
            return [$this->iri->get('livechat/{id}')];
        }

        return [$this->iri->get("livechat/$userId")];
    }

    public function getPublicTopics(): array
    {
        return [];
    }
}
