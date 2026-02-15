<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\ViewModel;

use Magento\Customer\Model\Url;
use Magento\Framework\View\Element\Block\ArgumentInterface;

readonly class CustomerUrl implements ArgumentInterface
{
    public function __construct(
        private Url $customerUrl
    ) {
    }

    public function getLoginUrl(): string
    {
        return $this->customerUrl->getLoginUrl();
    }
}
