define([
    'uiComponent',
    'ko',
    'jquery'
], function (Component, ko, $) {
    'use strict';

    return Component.extend({
        defaults: {
            conversationId: null,
            mercureUrl: '',
            messagesUrl: '',
            sendMessageUrl: '',
            markAsReadUrl: '',
            messages: [],
            messageText: '',
            isLoading: false,
            isSending: false,

            tracks: {
                messages: true,
                messageText: true,
                isLoading: true,
                isSending: true
            }
        },

        initialize: function () {
            this._super();
            this.loadMessages();
            this.connectMercure();

            return this;
        },

        loadMessages: function () {
            let self = this;
            this.isLoading = true;

            $.ajax({
                url: this.messagesUrl,
                type: 'GET',
                data: { id: this.conversationId, page: 1 },
                dataType: 'json',
                showLoader: false
            }).done(function (data) {
                self.messages = data;
                self.scrollToBottom();
                self.markAllAsRead();
            }).always(function () {
                self.isLoading = false;
            });
        },

        sendMessage: function () {
            let self = this,
                text = this.messageText.trim();

            if (!text || this.isSending) {
                return;
            }

            this.isSending = true;

            $.ajax({
                url: this.sendMessageUrl,
                type: 'POST',
                data: { id: this.conversationId, text: text, form_key: window.FORM_KEY },
                dataType: 'json'
            }).done(function () {
                self.messageText = '';
            }).fail(function () {
                // Error is displayed by Magento's global error handler
            }).always(function () {
                self.isSending = false;
            });
        },

        connectMercure: function () {
            let self = this,
                url = new URL(this.mercureUrl),
                topic = 'conversation_index_index_' + this.conversationId;

            url.searchParams.append('topic', topic);

            let eventSource = new EventSource(url.toString(), { withCredentials: true });

            eventSource.onmessage = function (event) {
                let payload = JSON.parse(event.data);

                if (payload.type === 'message:received') {
                    self.messages.push(payload.data);
                    self.scrollToBottom();
                    self.markAllAsRead();
                }

                if (payload.type === 'messages:read') {
                    self.onMessagesRead(payload.data.last_read_message_id);
                }
            };

            eventSource.onerror = function () {
                console.error('LiveChat Mercure connection error');
            };
        },

        markAllAsRead: function () {
            let messages = this.messages,
                lastMessage;

            if (!messages.length || !this.markAsReadUrl) {
                return;
            }

            lastMessage = messages[messages.length - 1];

            $.ajax({
                url: this.markAsReadUrl,
                type: 'POST',
                data: {
                    id: this.conversationId,
                    lastReadMessageId: lastMessage.id,
                    form_key: window.FORM_KEY
                },
                dataType: 'json',
                showLoader: false
            });
        },

        onMessagesRead: function (lastReadMessageId) {
            let messages = this.messages,
                updated = false,
                i, message;

            for (i = 0; i < messages.length; i++) {
                message = messages[i];

                if (parseInt(message.id, 10) <= lastReadMessageId && parseInt(message.status, 10) !== 1) {
                    message.status = 1;
                    updated = true;
                }
            }

            if (updated) {
                this.messages = messages.slice();
            }
        },

        scrollToBottom: function () {
            setTimeout(function () {
                let container = document.getElementById('livechat-messages');

                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 100);
        },

        showDateSeparator: function (index) {
            let messages = this.messages,
                current = new Date(messages[index].created_at + ' UTC').toDateString();

            if (index === 0) {
                return true;
            }

            let previous = new Date(messages[index - 1].created_at + ' UTC').toDateString();

            return current !== previous;
        },

        formatSeparatorDate: function (dateStr) {
            if (!dateStr) {
                return '';
            }

            let date = new Date(dateStr + ' UTC');

            return date.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric'
            });
        },

        formatTime: function (dateStr) {
            if (!dateStr) {
                return '';
            }

            let date = new Date(dateStr + ' UTC');

            return date.toLocaleString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        },

        isAdminMessage: function (message) {
            // sender_type 2 = admin (UserContextInterface::USER_TYPE_ADMIN)
            return parseInt(message.sender_type, 10) === 2;
        },

        isRead: function (message) {
            return parseInt(message.status, 10) === 1;
        },

        handleKeydown: function (data, event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                this.sendMessage();

                return false;
            }

            return true;
        }
    });
});
