# ROLE

You are acting as a Principal Software Architect, Senior Laravel Architect, Senior React Engineer, Product Manager, UI/UX Architect, Database Architect, DevOps Engineer, QA Engineer, Security Engineer, and Technical Lead.

Your responsibility is NOT to immediately start coding.

Your first responsibility is to deeply analyze this project, design the complete system, and create an implementation roadmap before writing a single feature.

This project must be built with production quality architecture and code standards.

--------------------------------------------------
PROJECT OVERVIEW
--------------------------------------------------

We are building a modern Multi-Vendor Handmade Marketplace.

Target sellers include:

- Resin Art
- Handmade Jewelry
- Necklaces
- Earrings
- Bracelets
- Bouquets
- Customized Gifts
- Crochet Products
- Candles
- Home Decor
- Calligraphy
- Personalized Accessories
- Gift Boxes
- Wedding Accessories
- Small Handmade Businesses

The goal is NOT to build another basic eCommerce website.

The goal is to build a premium marketplace specifically optimized for creators who currently sell through Instagram, TikTok, Facebook, and WhatsApp.

The system must focus on:

• beautiful UI
• excellent UX
• easy customization
• customer trust
• smooth ordering
• vendor management
• scalability
• high performance

--------------------------------------------------
TECH STACK
--------------------------------------------------

Backend

- Laravel (latest stable)
- PHP latest stable
- MySQL
- Redis
- Queue Jobs
- Laravel Events
- Notifications
- Policies
- Gates

Frontend

- React
- TypeScript
- Vite

UI

- Tailwind CSS
OR
Bootstrap (recommend whichever is better)

Animations

- Framer Motion (if appropriate)

Icons

- Lucide

State

- React Query
- Zustand (if needed)

Authentication

- Laravel Breeze or Laravel Sanctum

Media

- Spatie Media Library

Permissions

- Spatie Permission

Payments

Architecture must support:

Stripe

PayPal

Local Payment Gateways

Cash on Delivery

Shipping

Architecture must support:

FedEx

UPS

DHL

Local Shipping

Cloud Storage

Support:

S3

Cloudflare R2

Local

--------------------------------------------------
IMPORTANT
--------------------------------------------------

DO NOT start coding.

FIRST create documentation.

--------------------------------------------------
STEP 1
--------------------------------------------------

Create

docs/

inside it create

PROJECT_PLAN.md

This document should be extremely detailed.

Minimum sections:

# Vision

# Business Goals

# User Types

Guest

Customer

Vendor

Admin

Staff

# Complete Feature List

Everything that should exist in the platform.

# User Stories

# Functional Requirements

# Non Functional Requirements

# Architecture

# Folder Structure

# Database Design

Include every table.

Include every relationship.

Include indexes.

Include constraints.

# ER Diagram (markdown)

# API Design

# UI Pages

Every page.

Every dashboard.

Every modal.

Every form.

# Vendor Dashboard

# Customer Dashboard

# Admin Dashboard

# Order Workflow

# Product Workflow

# Custom Product Workflow

# Chat Workflow

# Notifications Workflow

# Search Workflow

# Security

# Permissions

# Performance

# Caching

# SEO

# Accessibility

# Mobile Responsive

# Future Features

# Risks

# Development Roadmap

Break project into many milestones.

--------------------------------------------------
FEATURES
--------------------------------------------------

Marketplace

Multi Vendor

Vendor Storefront

Vendor Verification

Featured Vendors

Vendor Ratings

Vendor Reviews

Vendor Followers

Vendor Analytics

Vendor Earnings

Vendor Withdrawals

Commission System

Subscription Plans

Store Themes

Product Catalog

Unlimited Products

Categories

Subcategories

Tags

Collections

Product Variants

Product Options

Product Attributes

Inventory

SKU

Barcode

Digital Products

Physical Products

Handmade Products

Custom Products

Personalized Products

Occasion Based Products

Product Videos

Multiple Images

360 Images

Image Zoom

Wishlist

Recently Viewed

Compare Products

Related Products

Recommended Products

Trending

Featured

Best Selling

New Arrivals

Flash Sales

Coupons

Gift Cards

--------------------------------------------------
CUSTOM PRODUCTS
--------------------------------------------------

Customer should be able to:

Upload inspiration photos

Upload logo

Upload handwriting

Enter custom text

Choose colors

Choose material

Choose size

Choose finishing

Choose packaging

Choose delivery date

Add notes

Vendor can approve

Vendor can reject

Vendor can request clarification

Vendor can send quotation

--------------------------------------------------
CHAT
--------------------------------------------------

Implement internal chat.

Customer ↔ Vendor

Vendor ↔ Admin

Customer ↔ Admin

Features:

Real-time

Read receipts

Typing indicator

Image sharing

File sharing

Product sharing

Order-linked chat

Notification

Message search

Pinned messages

Emoji

Future support:

Voice notes

Video calls

AI Assistant

--------------------------------------------------
ORDER MANAGEMENT
--------------------------------------------------

Cart

Checkout

Taxes

Shipping

Tracking

Returns

Refunds

Cancel

Exchange

Custom Orders

Partial Shipment

Invoices

Packing Slips

Order Timeline

--------------------------------------------------
CUSTOMER
--------------------------------------------------

Profile

Addresses

Wishlist

Order History

Saved Cards

Reviews

Notifications

Messages

Coupons

Referral Program

Loyalty Points

--------------------------------------------------
VENDOR
--------------------------------------------------

Dashboard

Sales

Revenue

Analytics

Products

Orders

Messages

Reviews

Inventory

Coupons

Customers

Payouts

Reports

--------------------------------------------------
ADMIN
--------------------------------------------------

Everything manageable.

--------------------------------------------------
UI / UX
--------------------------------------------------

Modern.

Premium.

Minimal.

Apple level quality.

Fast.

Elegant.

Beautiful.

Responsive.

Accessible.

Animation should be subtle.

--------------------------------------------------
STEP 2
--------------------------------------------------

After PROJECT_PLAN.md is completed,

STOP.

Wait for my approval.

DO NOT start coding.

--------------------------------------------------
STEP 3
--------------------------------------------------

After approval,

create

TASKS.md

Break the entire project into very small tasks.

Each task should be independently testable.

Each task should be completable in one session.

--------------------------------------------------
STEP 4
--------------------------------------------------

Then start implementation.

ONLY ONE TASK AT A TIME.

For every task:

1. Explain what will be built.

2. Explain why.

3. Implement.

4. Test.

5. Fix issues.

6. Mark task completed.

7. Commit-ready code.

Then stop.

Wait for approval.

Never jump ahead.

--------------------------------------------------
RULES
--------------------------------------------------

Always use:

SOLID

DRY

KISS

Clean Architecture

Repository Pattern where appropriate

Service Layer

Form Requests

Policies

Events

Jobs

Resource Controllers

API Resources

DTOs if needed

Proper Validation

Proper Error Handling

No duplicated logic.

No quick hacks.

No unnecessary packages.

Everything must be scalable.

Every feature should be documented.

Every migration reversible.

Every class documented.

Write production-ready code only.

If a better architecture decision exists than my suggestion, explain it before implementing and recommend the better approach.

Always think several steps ahead before writing code.