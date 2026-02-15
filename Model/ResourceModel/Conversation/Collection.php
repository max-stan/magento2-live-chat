<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model\ResourceModel\Conversation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MaxStan\LiveChat\Model\Conversation as Model;
use MaxStan\LiveChat\Model\ResourceModel\Conversation as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'livechat_conversations_collection';

    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
