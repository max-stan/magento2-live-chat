<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model\ResourceModel\Message;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MaxStan\LiveChat\Model\Message as Model;
use MaxStan\LiveChat\Model\ResourceModel\Message as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'livechat_messages_collection';

    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
