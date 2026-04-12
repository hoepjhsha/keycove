# 🔑 KeyCove - Smart Digital Product Trading Platform

> **Slogan:** *Unlock your world, Securely & Smartly.*

## 📖 Table of Contents
- [About the Project](#-about-the-project)
- [Business Model](#-business-model)
- [Core Value Proposition](#-core-value-proposition)
- [System Architecture](#-system-architecture)
- [Key Features](#-key-features)
- [User Roles & Workflows](#-user-roles--workflows)

---

## 🌍 About the Project

In the digital era, the demand for software, games (Steam, Epic, Origin), and online services (Netflix, Spotify) is growing rapidly. However, the current digital key trading market faces two major challenges:
1. **Fraud Risks:** Buyers worry about receiving used keys or keys locked to the wrong geographical region.
2. **Dispute Resolution Friction:** Existing platforms spend excessive time and human resources verifying evidence during disputes between buyers and sellers.

**KeyCove** solves this by providing a secure, automated, and escrow-backed e-commerce platform dedicated to digital products.

## 💼 Business Model

KeyCove operates as a **Multi-model E-commerce Platform**:
- **B2C (Business-to-Consumer):** The platform administration imports genuine keys and sells them directly to end-users.
- **C2C (Consumer-to-Consumer):** Individual users can register as vendors (sellers), list their unused digital keys, and create a vibrant exchange ecosystem.

## 💎 Core Value Proposition

- **For Buyers:** Purchase digital keys at competitive prices with absolute safety, guaranteed by the platform's Escrow protection mechanism.
- **For Sellers:** Access a streamlined platform to reach a broader customer base and manage cash flow professionally.

## 🏗 System Architecture

- **Architecture Pattern:** Domain-Driven Design (DDD) & Modular Monolith architecture, ensuring the system is highly maintainable, scalable, and easy to transition to microservices in the future if needed.
- **Security:** Database Encryption for keys, One-time Reveal mechanisms.

## ✨ Key Features

### 1. Identity & Access Management (IAM)
- **Authentication:** Email/Password and Social Login (Google, Facebook).
- **Security:** Multi-Factor Authentication (2FA) for enhanced security, especially for Sellers and Admins.
- **Authorization:** Role-Based Access Control (RBAC) defining Buyers, Sellers, and Admins.
- **Trust & Verification:** KYC (Know Your Customer) identity verification required for C2C Sellers.

### 2. Catalog & Inventory Management
- **Smart Categorization:** Filter by Games (Steam, Epic), Software (Office, Adobe), Services (Netflix, Spotify).
- **Product Attributes:** Granular tagging for Region (Global, SEA, Turkey, etc.) and Platform (PC, Console).
- **Secure Key Vault:**
  - Bulk key imports via Excel/CSV.
  - Database-level encryption for all stored keys.
  - **"One-time Reveal":** Purchased keys are displayed only once to the buyer to prevent compromise.
- **Inventory Tracking:** Automated low-stock warnings and auto-hiding of out-of-stock products.

### 3. Trading & Order Processing
- **Shopping Cart:** Add, remove, and update quantities from multiple sellers simultaneously.
- **Automated Fulfillment:** System instantly fetches the key from the secure vault and delivers it via UI/Email upon successful payment.
- **Payment Gateways:** Integrated with MoMo, VNPAY, and Stripe.
- **Order Tracking:** Detailed history of order statuses (Completed, Disputed, Refunded).

### 4. E-Wallet & Escrow System
- **Internal Wallet:** Store user balances and track transaction history.
- **Withdrawals:** Sellers can withdraw revenue to bank accounts (minus platform commission).
- **Escrow Mechanism (Tạm giữ tiền):** 
  - Buyer payments are held securely by the platform for a specific timeframe (e.g., 24-48 hours).
  - Funds are only released to the seller when the buyer confirms the key works, or when the dispute window expires.

### 5. Vendor Dashboard
- **Product Listing:** Intuitive interface for C2C sellers to create listings and upload keys.
- **Analytics:** Revenue growth charts and best-selling product tracking.
- **Order & Cashflow Management:** Track sold orders and monitor funds currently in Escrow.

### 6. Dispute & Support Center
- **Ticketing System:** Buyers can submit support requests for faulty keys, attaching video/image evidence.
- **Admin Intervention:** Administrators act as arbitrators, reviewing evidence from both parties to decide on a Refund (to buyer) or Release (to seller).
- **Rating System:** Post-transaction star ratings and reviews to build seller reputation.

### 7. Administration
- **User & Product Control:** Ban violating accounts, approve new product listings, and verify KYC requests.
- **Platform Configuration:** Adjust platform commission rates dynamically.
- **Global Reporting:** Comprehensive dashboards covering total platform revenue, transaction volumes, and dispute rates.

---

## 👥 User Roles & Workflows

### 🛒 Guest / Buyer
- Register / Login and manage personal profile & security.
- Browse catalog, view detailed product info (region, attributes, ratings).
- Manage cart and proceed to checkout.
- Receive "One-time Reveal" key immediately after payment.
- Leave reviews/ratings after successful transactions.
- Submit dispute tickets if a key is invalid.

### 🏪 Seller (Vendor)
- Complete KYC verification to upgrade from Buyer to Seller.
- Manage product categories and inventory (add/edit products, upload keys).
- Track sold orders and Escrow balance.
- Withdraw available wallet balance to a personal bank account.
- Respond to buyer complaints and disputes.
- View business analytics and sales statistics.

### 🛡️ Administrator
- Manage all users (lock violating accounts, change permissions).
- Approve C2C seller applications (KYC verification).
- Act as the final judge in dispute resolution.
- Configure system settings and platform commission fees.
- Monitor overall platform health and financial reports.
