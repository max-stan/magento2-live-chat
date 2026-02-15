<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ConversationSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \MaxStan\LiveChat\Api\Data\ConversationInterface[]
     */
    public function getItems();

    /**
     * @param \MaxStan\LiveChat\Api\Data\ConversationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
