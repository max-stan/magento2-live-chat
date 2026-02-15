<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Conversation extends AbstractDb
{
    /**
     * @var string
     */
    protected string $_eventPrefix = 'livechat_conversations_resource_model';

    protected function _construct(): void
    {
        $this->_init('livechat_conversations', 'id');
        $this->_useIsObjectNew = true;
    }
}
