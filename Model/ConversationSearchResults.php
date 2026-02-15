<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model;

use Magento\Framework\Api\SearchResults;
use MaxStan\LiveChat\Api\Data\ConversationSearchResultsInterface;

class ConversationSearchResults extends SearchResults implements ConversationSearchResultsInterface
{
}
