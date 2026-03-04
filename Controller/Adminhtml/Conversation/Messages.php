<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Controller\Adminhtml\Conversation;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use MaxStan\LiveChat\Api\MessagesManagerInterface;
use MaxStan\LiveChat\Model\Message;
use MaxStan\Mercure\Api\MercureHttpManagementInterface;

class Messages extends Action implements HttpGetActionInterface
{
    public const string ADMIN_RESOURCE = 'MaxStan_LiveChat::livechat_manage';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly MessagesManagerInterface $chatManagement,
        private readonly MercureHttpManagementInterface $mercureHttpManagement
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $conversationId = (int)$this->getRequest()->getParam('id');
        $currentPage = (int)($this->getRequest()->getParam('page', 1));
        $result = $this->jsonFactory->create();

        try {
            /** @var Message[] $messages */
            $messages = $this->chatManagement->get($conversationId, $currentPage);
            $data = array_map(fn ($message) => $message->getData(), $messages);
            $result->setData($data);
            $this->mercureHttpManagement->attachAuthorizationCookie();
        } catch (Exception $e) {
            $result->setHttpResponseCode(400);
            $result->setData(['error' => true, 'message' => $e->getMessage()]);
        }

        return $result;
    }
}
