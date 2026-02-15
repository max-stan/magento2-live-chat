<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface MessageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \MaxStan\LiveChat\Api\Data\MessageInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return \MaxStan\LiveChat\Api\Data\MessageSearchResultsInterface
     */
    public function setItems(array $items);
}
