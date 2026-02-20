<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Test\Integration\Mock;

use Magento\Authorization\Model\UserContextInterface;

class UserContextStub implements UserContextInterface
{
    private ?int $userId = null;
    private ?int $userType = null;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getUserType(): ?int
    {
        return $this->userType;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function setUserType(?int $userType): void
    {
        $this->userType = $userType;
    }

    public function reset(): void
    {
        $this->userId = null;
        $this->userType = null;
    }
}
