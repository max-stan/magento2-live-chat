<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\Api\Data;

interface PublicMessageInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param $id
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getConversationId(): int;

    /**
     * @param int $conversationId
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     */
    public function setConversationId(int $conversationId): PublicMessageInterface;

    /**
     * @return string
     */
    public function getText(): string;

    /**
     * @param string $text
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     */
    public function setText(string $text): PublicMessageInterface;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     */
    public function setStatus(int $status): PublicMessageInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     */
    public function setCreatedAt(string $createdAt): PublicMessageInterface;

    /**
     * @return string
     */
    public function getSenderName(): string;

    /**
     * @param string $senderName
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     */
    public function setSenderName(string $senderName): PublicMessageInterface;

    /**
     * @return int
     */
    public function getSenderType(): int;

    /**
     * @param int $own
     * @return \MaxStan\LiveChat\Api\Data\PublicMessageInterface
     */
    public function setSenderType(int $senderType): PublicMessageInterface;
}
