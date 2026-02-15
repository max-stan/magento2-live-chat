<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use MaxStan\LiveChat\Api\Data\ConversationInterface;
use MaxStan\LiveChat\Model\ResourceModel\Conversation as ResourceModel;

class Conversation extends AbstractModel implements ConversationInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'livechat_conversations_model';

    /**
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    public function getUserId(): int
    {
        return (int)$this->getData(self::USER_ID);
    }

    /**
     * Setter for UserId.
     *
     * @param int|null $userId
     *
     * @return void
     */
    public function setUserId(int $userId): ConversationInterface
    {
        $this->setData(self::USER_ID, $userId);

        return $this;
    }

    public function getAdminId(): ?int
    {
        return $this->getData(self::ADMIN_ID) === null ? null
            : (int)$this->getData(self::ADMIN_ID);
    }

    public function setAdminId(?int $adminId): ConversationInterface
    {
        $this->setData(self::ADMIN_ID, $adminId);

        return $this;
    }

    public function getCreatedAt(): string
    {
        return (string)$this->getData(self::CREATED_AT);
    }

    public function setCreatedAt(string $createdAt): ConversationInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);

        return $this;
    }
}
