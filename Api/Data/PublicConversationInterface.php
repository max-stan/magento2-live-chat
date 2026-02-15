<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Api\Data;

interface PublicConversationInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param $id
     * @return \MaxStan\LiveChat\Api\Data\ConversationInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return \MaxStan\LiveChat\Api\Data\ConversationInterface
     */
    public function setCreatedAt(string $createdAt): PublicConversationInterface;

    /**
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface[]|null
     */
    public function getMessages(): ?array;

    /**
     * @param \MaxStan\LiveChat\Api\Data\PublicMessageInterface[] $messages
     * @return \MaxStan\LiveChat\Api\Data\ConversationInterface
     */
    public function setMessages(array $messages): PublicConversationInterface;
}
