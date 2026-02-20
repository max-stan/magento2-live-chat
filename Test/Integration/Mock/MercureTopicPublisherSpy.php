<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Test\Integration\Mock;

use MaxStan\Mercure\Service\MercureTopicPublisher;

class MercureTopicPublisherSpy extends MercureTopicPublisher
{
    private array $publishedMessages = [];

    public function __construct()
    {
        // Skip parent constructor — we don't need real Mercure dependencies
    }

    public function execute(string $topic, mixed $data): string
    {
        $this->publishedMessages[] = ['topic' => $topic, 'data' => $data];

        return 'urn:uuid:mock-' . count($this->publishedMessages);
    }

    public function getPublishedMessages(): array
    {
        return $this->publishedMessages;
    }

    public function getLastPublishedMessage(): ?array
    {
        return end($this->publishedMessages) ?: null;
    }

    public function reset(): void
    {
        $this->publishedMessages = [];
    }
}
