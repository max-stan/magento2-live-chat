<?php

declare(strict_types=1);

namespace MaxStan\LiveChat\ViewModel\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use MaxStan\LiveChat\Api\ConversationRepositoryInterface;
use MaxStan\Mercure\Model\Iri;

readonly class ConversationView implements ArgumentInterface
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private RequestInterface $request,
        private Iri $iri
    ) {
    }

    public function getConversationId(): int
    {
        return (int)$this->request->getParam('id');
    }

    public function getIri(): string
    {
        try {
            $conversation = $this->conversationRepository->getById($this->getConversationId());

            $userId = $conversation->getUserId();
            return $this->iri->get("livechat/$userId");
        } catch (NoSuchEntityException) {
            return '';
        }
    }
}
