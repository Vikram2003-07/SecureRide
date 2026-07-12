# SecureRide

> A **PHP + MySQL** cab-booking application that ships in **two parallel versions** — one intentionally vulnerable for learning, and one hardened as the reference implementation. Built as a hands-on training ground for web application security.

![Tech](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php&logoColor=white)
![Tech](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)
![Tech](https://img.shields.io/badge/Bootstrap-UI-7952B3?logo=bootstrap&logoColor=white)
![Purpose](https://img.shields.io/badge/Purpose-Educational-e94560)
![Status](https://img.shields.io/badge/Status-Active-success)

---

## Table of Contents

- [Overview](#overview)
- [Why Two Versions?](#why-two-versions)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Setup Instructions](#setup-instructions)
- [Usage Guide](#usage-guide)
- [Vulnerabilities Demonstrated](#vulnerabilities-demonstrated)
- [Security Defenses Implemented](#security-defenses-implemented)
- [Default Credentials](#default-credentials)
- [Tools for Testing](#tools-for-testing)
- [Screenshots & Walkthrough](#screenshots--walkthrough)
- [Contributing](#contributing)
- [License](#license)
- [Disclaimer](#disclaimer)

---

## Overview

**SecureRide** is a full-stack web application that simulates a real-world cab-booking platform. The same product is built twice:

| Version | Folder | Purpose |
|---|---|---|
| **Vulnerable** | `cab_vulnerable/` | A "learn by breaking" build with common web flaws intentionally left in |
| **Secure** | `cab_secure/` | The hardened, production-grade reference implementation |

Both versions share the same UI, features, and database — so you can perform the same attack on each and see the difference in behavior.

---

## Why Two Versions?

Most security tutorials explain vulnerabilities in isolation. SecureRide instead puts them inside a **realistic, feature-complete application** so you can:

- 🎯 **Attack** the vulnerable build with real exploits (SQLi, XSS, IDOR, CSRF, RCE, etc.)
- 🛡️ **Compare** the secure build side-by-side to understand the fix
- 📚 **Learn** by reading the inline `⚠️ VULNERABILITY` / `✅ SECURITY` comments in the code
- 🧪 **Test** the same payloads against both versions using Burp Suite, sqlmap, browser DevTools, etc.

---

## Features

### User Features
- 📝 User registration & login (with profile picture upload)
- 🚕 Book a cab (pickup → dropoff)
- 💰 Live fare calculation
- 👨‍✈️ Browse available drivers (with rating, car model, status)
- 📜 Ride history with status tracking
- ⭐ Post reviews and ratings after completed rides
- 💳 Payment flow simulation (UPI, Credit Card, Debit Card, Cash)
- 👤 Profile management (phone, avatar, password)

### Admin Features
- 🔐 Separate admin login portal
- 📊 Dashboard with booking statistics
- 📋 Manage all bookings (confirm, cancel, complete)
- 🧑‍✈️ Manage drivers (add, edit, status: available/busy/offline)
- 💸 Configure UPI payment ID & QR code
- 🔑 Admin password reset

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8+ |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, CSS3, Bootstrap, vanilla JS |
| **Auth** | PHP Sessions |
| **Web Server** | Apache (via XAMPP / WAMP / Laragon) |
| **Icons/UI** | Bootstrap Icons, custom CSS |

---

## Project Structure

```
SecureRide/
├── index.php                  # Landing page — choose vulnerable or secure demo
├── SecureRide.sql            # Database schema + sample data
├── README.md                  # This file
│
├── cab_vulnerable/            # ⚠️ Intentionally insecure version
│   ├── booking.php            # Book a cab (IDOR, no CSRF)
│   ├── dashboard.php
│   ├── drivers.php
│   ├── history.php
│   ├── login.php              # SQL injection in login
│   ├── logout.php
│   ├── payment.php            # Fare tampering possible
│   ├── profile.php            # XSS in profile fields
│   ├── register.php           # Stores plaintext passwords
│   ├── reviews.php            # Stored XSS in comments
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── login.php
│   │   ├── manage_bookings.php
│   │   ├── manage_drivers.php
│   │   ├── manage_upi.php
│   │   └── logout.php
│   ├── api/
│   │   └── fare.php           # Client-side fare — interceptable
│   ├── assets/
│   │   ├── css/style.css
│   │   └── js/script.js
│   ├── includes/
│   │   ├── config.php
│   │   ├── header.php
│   │   └── footer.php
│   └── uploads/               # ⚠️ Contains proof-of-concept exploit artifacts
│       ├── shell.php
│       ├── csrf_attack.html
│       ├── index.php
│       ├── t.txt
│       └── upi_qr_path.txt
│
└── cab_secure/                # ✅ Hardened reference implementation
    ├── booking.php
    ├── dashboard.php
    ├── drivers.php
    ├── history.php
    ├── landing.php
    ├── login.php              # Prepared statements, password_verify
    ├── logout.php
    ├── payment.php
    ├── profile.php            # Output escaping, validation
    ├── register.php           # password_hash, input validation
    ├── reviews.php            # htmlspecialchars on output
    ├── admin/
    │   ├── check_admin.php
    │   ├── dashboard.php
    │   ├── login.php
    │   ├── logout.php
    │   ├── manage_bookings.php
    │   ├── manage_drivers.php
    │   ├── manage_upi.php
    │   └── reset_password.php
    ├── assets/
    │   └── css/style.css
    ├── includes/
    │   ├── auth.php           # 🔐 require_auth, require_admin, IDOR guards
    │   ├── config.php
    │   ├── header.php
    │   └── footer.php
    └── uploads/
        ├── upi_id.txt
        └── upi_qr_path.txt
```

---

## Database Schema

The database `SecureRide` contains **6 tables**:

| Table | Purpose | Key Columns |
|---|---|---|
| `users` | End-user accounts | `id`, `username`, `email`, `password` (hash), `phone`, `profile_pic` |
| `admins` | Admin accounts | `id`, `username`, `password` (hash), `email` |
| `drivers` | Cab drivers | `id`, `name`, `phone`, `car_model`, `car_number`, `rating`, `status` |
| `bookings` | Ride records | `id`, `user_id`, `driver_id`, `pickup_location`, `dropoff_location`, `fare`, `status` |
| `payments` | Payment transactions | `id`, `booking_id`, `user_id`, `amount`, `payment_method`, `transaction_id`, `status` |
| `reviews` | Post-ride ratings | `id`, `booking_id`, `user_id`, `driver_id`, `rating` (1–5), `comment` |

---

## Setup Instructions

### Prerequisites
- PHP **8.0+**
- MySQL / MariaDB
- Apache (XAMPP, WAMP, MAMP, or Laragon recommended)

### Step-by-step

1. **Start your local server stack** (Apache + MySQL).
2. **Create the database**:
   ```sql
   CREATE DATABASE SecureRide;
   ```
3. **Import the schema and sample data**:
   ```bash
   # From the project root:
   mysql -u root -p SecureRide < SecureRide.sql
   ```
   Or use **phpMyAdmin** → Import → select `SecureRide.sql`.
4. **Configure DB credentials** (if different from `root` / no password):
   - `cab_vulnerable/includes/config.php`
   - `cab_secure/includes/config.php`
5. **Place the project** in your web root:
   ```
   htdocs/SecureRide/        # XAMPP
   www/SecureRide/           # WAMP
   ```
6. **Open in browser**:
   ```
   http://localhost/SecureRide/
   ```
7. **Choose** a version from the landing page.

---

## Usage Guide

1. **Landing page** (`/SecureRide/`) → click *Vulnerable Demo* or *Secure Demo*
2. **Register** a new account (or log in with seed credentials below)
3. **Browse drivers** → pick one → enter pickup & dropoff → **Book**
4. **Pay** using the simulated gateway
5. **Review** after ride completion
6. **Admin** → log in at `/{vulnerable|secure}/admin/login.php` to manage the system

---

## Vulnerabilities Demonstrated

The `cab_vulnerable/` version contains these intentional flaws:

| # | Vulnerability | Where to Find | How to Exploit |
|---|---|---|---|
| 1 | **SQL Injection** | `login.php`, `register.php` | `' OR '1'='1` in username field |
| 2 | **Stored XSS** | `reviews.php` | Post a comment like `<script>alert(1)</script>` |
| 3 | **Reflected XSS** | `search` / URL params | Inject JS via query parameters |
| 4 | **Plaintext Passwords** | `users` table (seed) | Dump DB to read credentials |
| 5 | **IDOR** | `booking.php? id=`, `payment.php?id=` | Change `?id=` to access other users' bookings |
| 6 | **Fare Tampering** | `api/fare.php` + `booking.php` | Intercept response with Burp → set fare to `0.01` |
| 7 | **CSRF** | `payment.php`, `profile.php` | Host `csrf_attack.html` to trigger actions |
| 8 | **File Upload RCE** | `profile.php` (avatar) | Upload `shell.php` instead of an image |
| 9 | **Path Traversal** | `profile_pic` column | Set `profile_pic` to `../index.php` |
| 10 | **Missing Access Control** | `admin/` pages | Access admin URLs without role check |
| 11 | **Session Issues** | `auth.php` (missing) | No `session_regenerate_id`, no expiry |
| 12 | **Open Redirect** | login redirect params | `?redirect=//evil.com` |
| 13 | **Insecure Direct Object Reference on UPI** | `manage_upi.php` | Modify other admins' settings |

> 💡 **Tip:** The `cab_vulnerable/uploads/` folder already contains `shell.php` and `csrf_attack.html` — artifacts of previous exploitation sessions for reference.

---

## Security Defenses Implemented

The `cab_secure/` version applies these fixes:

| Defense | Implementation |
|---|---|
| **Prepared Statements** | All DB queries use `?` placeholders via PDO |
| **Password Hashing** | `password_hash()` + `password_verify()` (bcrypt) |
| **Output Escaping** | `htmlspecialchars()` on all user-controlled output |
| **Authentication Guards** | `require_auth()` / `require_admin()` on every page |
| **IDOR Protection** | `verify_booking_owner()` checks ownership server-side |
| **CSRF Tokens** | Token-per-form + server-side validation |
| **Session Hardening** | `session_regenerate_id()`, strict mode, secure cookies |
| **File Upload Validation** | Whitelist MIME types, random filenames, no execution in upload dir |
| **Input Validation** | Helpers: `validate_email()`, `validate_phone()`, `validate_password()` |
| **Role-Based Access Control** | Admin vs. user separation with separate session keys |
| **Secure Redirects** | Whitelist-based redirect targets |

---

## Default Credentials

> ⚠️ **For educational/lab use only.**

| Role | Username | Password | Notes |
|---|---|---|---|
| **Admin** | `admin` | `admin123` | Hashed in DB (`$2y$10$...`) — check `admins` table |
| **User (old — plaintext)** | `john_doe` | `password123` | Seeded plaintext to demonstrate the issue |
| **User (old — plaintext)** | `jane_smith` | `password456` | |
| **User (old — plaintext)** | `bob_wilson` | `password789` | |
| **User (new — hashed)** | `vevion` | *(set during registration)* | Uses bcrypt hash |
| **User (new — hashed)** | `vikram` | *(set during registration)* | |

> The mix of plaintext and hashed passwords in the `users` table is **intentional** — it illustrates a partial migration scenario.

---

## Tools for Testing

Recommended tools to exploit & verify the vulnerable version:

- 🌐 **Browser DevTools** — tamper with API responses and form fields
- 🦊 **Burp Suite Community** — intercept & modify HTTP requests
- 🗃️ **sqlmap** — automated SQL injection
- 🧅 **OWASP ZAP** — web app vulnerability scanner
- 🐚 **Browser Console** — test XSS payloads
- 🐧 **Kali Linux / Parrot OS** — full pentesting distro

---

## Screenshots & Walkthrough

> Add screenshots here after running the app — recommended shots:
> - Landing page (vulnerable vs. secure cards)
> - Login page (both versions)
> - Booking flow
> - Admin dashboard
> - Fare-tamper demo in Burp Suite
> - XSS alert in vulnerable reviews

---

## Contributing

Contributions are welcome! Some ideas:

- 🐛 **Report vulnerabilities** found in the secure version (yes, really)
- ✨ **Add new attack scenarios** to the vulnerable version
- 🛡️ **Add new defenses** to the secure version
- 📝 **Improve documentation** or add screenshots
- 🌐 **Translate** the README

### How to contribute
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-attack`)
3. Commit your changes
4. Push and open a Pull Request

---

## License

This project is released under the **MIT License** — see `LICENSE` for details.

```
MIT License

Copyright (c) 2026 SecureRide Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software...
```

---

## Disclaimer

> ⚠️ **This project is for educational and defensive security purposes only.**
>
> The vulnerable version contains intentional security flaws. **Do not deploy it to a public server or production environment.** Always run it in an isolated local lab.
>
> The authors are not responsible for any misuse of this code. Use it to **learn, teach, and build safer applications** — not to attack systems you don't own.

---

**Happy (ethical) hacking! 🚕🔐**