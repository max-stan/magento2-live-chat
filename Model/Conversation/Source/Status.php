<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Model\Conversation\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    public const int PENDING = 0;
    public const int ONGOING = 1;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::ONGOING, 'label' => __('Ongoing')],
        ];
    }
}
