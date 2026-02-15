<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Message extends AbstractDb
{
    protected string $_eventPrefix = 'livechat_messages_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct(): void
    {
        $this->_init('livechat_messages', 'id');
        $this->_useIsObjectNew = true;
    }
}
