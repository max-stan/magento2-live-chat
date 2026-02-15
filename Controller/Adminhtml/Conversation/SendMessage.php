<?php
declare(strict_types=1);

namespace MaxStan\LiveChat\Controller\Adminhtml\Conversation;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use MaxStan\LiveChat\Api\UserChatManagementInterface;

class SendMessage extends Action implements HttpPostActionInterface
{
    public const string ADMIN_RESOURCE = 'MaxStan_LiveChat::livechat_manage';

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly UserChatManagementInterface $chatManagement
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        $conversationId = (int)$this->getRequest()->getParam('id');
        $text = (string)$this->getRequest()->getParam('text');
        $result = $this->jsonFactory->create();

        try {
            $message = $this->chatManagement->sendMessage($conversationId, $text);
            $result->setData($message->getData());
        } catch (\Exception $e) {
            $result->setHttpResponseCode(400);
            $result->setData(['error' => true, 'message' => $e->getMessage()]);
        }

        return $result;
    }
}
