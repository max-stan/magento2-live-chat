<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Test\Integration\Mock;

use MaxStan\Mercure\Api\MercureHubInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\TokenProviderInterface;
use Symfony\Component\Mercure\MockHub;
use Symfony\Component\Mercure\Jwt\StaticTokenProvider;

class MercureHubStub implements MercureHubInterface
{
    public function getMercureHub(?int $customerId = null): HubInterface
    {
        return new MockHub(
            'https://mercure.test/.well-known/mercure',
            new StaticTokenProvider('test-jwt-token'),
            static fn () => 'urn:uuid:mock'
        );
    }

    public function getTokenProvider(): TokenProviderInterface
    {
        return new StaticTokenProvider('test-jwt-token');
    }

    public function setAuthorizationHeader(): void
    {
        // no-op in tests
    }
}
