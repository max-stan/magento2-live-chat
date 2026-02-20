<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model\ResourceModel\Conversation\Grid;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use MaxStan\LiveChat\Model\ResourceModel\Conversation\Collection as ConversationCollection;
use Psr\Log\LoggerInterface;

class Collection extends ConversationCollection implements SearchResultInterface
{
    private ?AggregationInterface $aggregations = null;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        string $mainTable,
        string $eventPrefix,
        string $eventObject,
        string $resourceModel,
        string $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    protected function _initSelect(): self
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['ce' => $this->getTable('customer_entity')],
            'main_table.user_id = ce.entity_id',
            ['customer_name' => new \Magento\Framework\DB\Sql\Expression("CONCAT(ce.firstname, ' ', ce.lastname)")]
        );

        $this->getSelect()->joinLeft(
            ['lm' => $this->getTable('livechat_messages')],
            'main_table.id = lm.conversation_id',
            [
                'message_count' => new \Magento\Framework\DB\Sql\Expression('COUNT(lm.id)'),
                'last_message' => new \Magento\Framework\DB\Sql\Expression('SUBSTRING(MAX(CONCAT(lm.created_at, "||", lm.text)), LOCATE("||", MAX(CONCAT(lm.created_at, "||", lm.text))) + 2)')
            ]
        );

        $this->getSelect()->group('main_table.id');

        return $this;
    }

    public function getAggregations(): AggregationInterface
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations): self
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    public function getSearchCriteria(): ?SearchCriteriaInterface
    {
        return null;
    }

    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null): self
    {
        return $this;
    }

    public function getTotalCount(): int
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount): self
    {
        return $this;
    }

    public function setItems(?array $items = null): self
    {
        return $this;
    }
}
