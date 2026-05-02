# KeyCove

KeyCove is a digital product trading platform focused on C2C key sales, order management, escrow handling, dispute resolution, and platform administration.

## Overview

KeyCove supports the core workflows of a digital key marketplace:

- User registration, login, and profile management
- Product browsing and detail viewing
- Shopping cart and checkout
- Online payment and secure key delivery
- Seller onboarding with KYC verification
- Complaint handling, dispute resolution, and platform configuration

## Technology Stack

- Laravel 13
- Livewire 4
- Tailwind CSS 4
- Pest 4
- PHP 8.4
- MySQL

## User Roles

- Guest: browse products and register an account
- Buyer: checkout, receive keys, review products, submit complaints
- Seller: list products, manage keys, track sales, withdraw funds
- Admin: approve sellers, manage users, resolve disputes, configure the platform

## Core Features

### Customer Features

- Register and sign in
- Manage personal profile and security settings
- Browse products and view product details
- Manage cart and place orders
- Pay online
- Receive the key after successful payment
- View order history
- Leave ratings, comments, and complaints

### Seller Features

- Apply to become a seller with KYC verification
- Manage categories and products
- Update key inventory
- Track orders and escrow status
- Manage wallet balance and withdraw funds
- Review sales analytics

### Admin Features

- Manage users
- Approve sellers
- Handle disputes and complaints
- View aggregate reports
- Configure fees and platform settings

## Order Flow

1. A buyer adds a product to the cart.
2. The system creates an order during checkout.
3. The payment gateway confirms the transaction.
4. The order status is updated.
5. The key is securely delivered to the buyer.
6. If issues occur, the buyer can submit a complaint.
7. Admin or seller resolves the case according to the workflow.

## Project Documentation

- `BAO_CAO_USE_CASE_FLOW_SAN_C2C.md`
- `USE_CASE_DIAGRAM_SAN_C2C.md`
- `SRS_VA_ACTIVITY_FLOW_SAN_C2C.md`

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Run the Project

```bash
composer run dev
```

Or run separately:

```bash
php artisan serve
npm run dev
```

## Testing

```bash
php artisan test
```

## Notes

- Some business flows are described from a system-analysis perspective and may require adaptation to the actual implementation.
- Additional UML diagrams can be derived from the existing documentation set.
