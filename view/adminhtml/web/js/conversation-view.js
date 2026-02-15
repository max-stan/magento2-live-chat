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
            const self = this;
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
            }).always(function () {
                self.isLoading = false;
            });
        },

        sendMessage: function () {
            const self = this,
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
            const self = this,
                url = new URL(this.mercureUrl),
                topic = 'conversation_index_index_' + this.conversationId;

            url.searchParams.append('topic', topic);

            var eventSource = new EventSource(url.toString(), { withCredentials: true });

            eventSource.onmessage = function (event) {
                var payload = JSON.parse(event.data);

                if (payload.type === 'message:received') {
                    self.messages.push(payload.data);
                    self.scrollToBottom();
                }
            };

            eventSource.onerror = function () {
                console.error('LiveChat Mercure connection error');
            };
        },

        scrollToBottom: function () {
            setTimeout(function () {
                var container = document.getElementById('livechat-messages');

                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 100);
        },

        showDateSeparator: function (index) {
            const messages = this.messages,
                current = new Date(messages[index].created_at + ' UTC').toDateString();

            if (index === 0) {
                return true;
            }

            const previous = new Date(messages[index - 1].created_at + ' UTC').toDateString();

            return current !== previous;
        },

        formatSeparatorDate: function (dateStr) {
            if (!dateStr) {
                return '';
            }

            var date = new Date(dateStr + ' UTC');

            return date.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric'
            });
        },

        formatTime: function (dateStr) {
            if (!dateStr) {
                return '';
            }

            var date = new Date(dateStr + ' UTC');

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
            return message.status === 'read';
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
