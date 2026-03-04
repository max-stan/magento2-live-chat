<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\CustomerData\Customer;

/**
 * Adds customer entity ID to the customer section data for LiveChat topic subscription.
 */
class AddCustomerIdToSectionData
{
    public function __construct(
        private readonly UserContextInterface $userContext
    ) {
    }

    public function afterGetSectionData(Customer $subject, array $result): array
    {
        $customerId = $this->userContext->getUserId();

        if ($customerId) {
            $result['uid'] = $customerId;
        }

        return $result;
    }
}
