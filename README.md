# Magento 2 Live Chat

> Real-time customer support chat module for Magento 2, powered by [Symfony Mercure](https://github.com/symfony/mercure) SSE messaging. Built for [Hyva Themes](https://hyva.io) with Alpine.js and Tailwind CSS.

[![Packagist](https://img.shields.io/packagist/v/max-stan/magento2-live-chat?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-live-chat)
[![Packagist](https://img.shields.io/packagist/dt/max-stan/magento2-live-chat?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-live-chat)
[![Packagist](https://img.shields.io/packagist/dm/max-stan/magento2-live-chat?style=for-the-badge)](https://packagist.org/packages/max-stan/magento2-live-chat)
[![Tests](https://img.shields.io/github/actions/workflow/status/max-stan/magento2-live-chat/main.yml?branch=master&style=for-the-badge&label=tests)](https://github.com/MaxStan/magento2-live-chat/actions/workflows/main.yml)

## Overview

MaxStan_LiveChat adds a full-featured live chat widget to the Magento storefront and a conversation management interface 
to the admin panel. Customers can open conversations, send messages, and receive real-time replies from store
administrators — all without page reloads, using server-sent events via Mercure.

### Key Features

- Real-time messaging between customers and admins via Mercure SSE
- Storefront chat widget with Alpine.js (Hyva-compatible)
- SharedWorker-based subscription management across browser tabs
- Admin grid for listing and managing conversations
- Admin conversation view with live message updates
- Per-conversation Mercure topic authorization (private channels)
- Conversation limit enforcement (max 10 per customer)
- Paginated message history (50 messages per page)
- Integration test suite with fixtures, mocks, and spies

## Installation

```shell
composer require max-stan/magento2-live-chat
bin/magento module:enable MaxStan_LiveChat
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

## Contributing

Contributions are welcome! If you find a bug or have a feature request, feel free to open an issue or submit a pull request.
