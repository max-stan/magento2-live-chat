<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\Api\Data;

interface MessageInterface
{
    public const string SENDER_ID = "sender_id";
    public const string CONVERSATION_ID = "conversation_id";
    public const string TEXT = "text";
    public const string STATUS = "status";
    public const string CREATED_AT = "created_at";
    public const string OWN = "own";

    /**
     * @return int
     */
    public function getId();

    /**
     * @param $id
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getSenderId(): int;

    /**
     * @param int $senderId
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     */
    public function setSenderId(int $senderId): MessageInterface;

    /**
     * @return int
     */
    public function getConversationId(): int;

    /**
     * @param int $conversationId
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     */
    public function setConversationId(int $conversationId): MessageInterface;

    /**
     * @return string
     */
    public function getText(): string;

    /**
     * @param string $text
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     */
    public function setText(string $text): MessageInterface;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     */
    public function setStatus(int $status): MessageInterface;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface
     */
    public function setCreatedAt(string $createdAt): MessageInterface;
}
