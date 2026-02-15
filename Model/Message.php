<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use MaxStan\LiveChat\Api\Data\MessageInterface;
use MaxStan\LiveChat\Model\ResourceModel\Message as ResourceModel;

class Message extends AbstractModel implements MessageInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'livechat_messages_model';

    /**
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    public function getSenderId(): int
    {
        return (int)$this->getData(self::SENDER_ID);
    }

    public function setSenderId(int $senderId): MessageInterface
    {
        $this->setData(self::SENDER_ID, $senderId);

        return $this;
    }

    public function getConversationId(): int
    {
        return (int)$this->getData(self::CONVERSATION_ID);
    }

    public function setConversationId(int $conversationId): MessageInterface
    {
        $this->setData(self::CONVERSATION_ID, $conversationId);

        return $this;
    }

    public function getText(): string
    {
        return (string)$this->getData(self::TEXT);
    }

    public function setText(string $text): MessageInterface
    {
        $this->setData(self::TEXT, $text);

        return $this;
    }

    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    public function setStatus(int $status): MessageInterface
    {
        $this->setData(self::STATUS, $status);

        return $this;
    }

    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): MessageInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);

        return $this;
    }
}
