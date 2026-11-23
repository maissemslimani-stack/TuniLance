# TuniLance

> A secure freelance marketplace and milestone escrow platform built for the Tunisian market, featuring native Tunisian Dinar (TND) wallet management, real-time negotiation chat, human moderator arbitration, and AI-assisted profile onboarding.

---

## Overview

Freelancers and businesses in Tunisia often face structural friction on international platforms due to foreign currency constraints (USD/EUR), cross-border payment barriers, informal scope negotiations, and limited local dispute protection. **TuniLance** bridges this gap by offering a structured collaboration workflow in TND—from project posting and talent matchmaking to milestone-secured escrow payments and human-verified dispute resolution.

---

## Key Features

### Role-Based Architecture (Freelancer, Client, Moderator)
* **Freelancer Workspace**
  * Browse and filter active job postings by category, expertise level, budget (TND), and project duration.
  * Submit proposals with custom bid amounts, delivery timelines, and cover letters, or respond to direct client invitations.
  * Manage active contracts, propose milestone breakdowns, and upload deliverables with notes and external links.
  * Track available balance, escrowed funds, and configure local withdrawal methods (Bank Transfer, D17 Wallet).
* **Client Workspace**
  * Publish projects, manage milestones, and browse/filter freelancers by category and skills to send direct project invitations.
  * Fund milestone escrow upfront and track project spending and transaction history.
  * Review submitted deliverables to either release escrowed funds or request revisions (automatically shifting downstream milestone due dates).
* **Moderator Workspace**
  * Review and verify newly registered client accounts to display trust badges across the marketplace.
  * Investigate and arbitrate disputes escalated via chat (`@moderator`) or early contract termination, with three resolution outcomes: release funds to freelancer, refund client, or split the escrowed amount.

### Core Engineering Highlights
* **Milestone Escrow & Atomic Financial Transactions:** Client funds are locked per milestone upon funding and released upon deliverable approval using atomic database transactions and row-level locking (`lockForUpdate`) to prevent race conditions and balance inconsistencies.
* **Real-Time Messaging & Structured Proposals:** Live WebSocket communication powered by **Laravel Reverb**, featuring inline `@proposal` negotiation cards (`@accept` / decline) permanently logged in conversation history for auditability.
* **AI-Assisted CV Autofill:** Multi-step onboarding supporting PDF CV uploads, combining local parsing (regex and skill keyword matching) with the **Hugging Face Inference API** to generate a concise professional bio and pre-fill user skills.

---

## Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Backend** | PHP 8+, Laravel (MVC, Eloquent ORM, Migrations, Broadcasting) |
| **Frontend** | Livewire, Blade, Tailwind CSS |
| **Real-Time** | Laravel Reverb (WebSockets) |
| **Database** | PostgreSQL |
| **AI Integration** | Hugging Face Inference API + Regex/Skill Extraction |

---

## Core Domain Model

The relational schema revolves around twelve core entities handling marketplace discovery, contract execution, financial locking, and moderation:

* **Identity & Finance:** `User` (Freelancer, Client, Moderator), `Wallet` (`balance`, `held_balance`)
* **Matchmaking:** `Project`, `Submission`, `Invitation`
* **Execution & Escrow:** `Contract`, `Milestone`, `Escrow` (`funded_amount`, `platform_fee`), `Deliverable`
* **Communication & Governance:** `ChatRoom`, `ChatMessage` (`is_proposal`, `proposal_status`), `Dispute`

---

## Getting Started

### Prerequisites
* PHP >= 8.2 & Composer
* Node.js & npm
* PostgreSQL

### Installation

1. **Clone the repository**
   ```bash
   git clone [https://github.com/](https://github.com/)<your-username>/tunilance.git
   cd tunilance