# SecureRide

SecureRide is a PHP + MySQL web application that demonstrates two versions of the same cab-booking system:

- Vulnerable version: intentionally insecure, for learning and testing common web flaws
- Secure version: hardened implementation with safer authentication, validation, and session handling

## Project overview

This workspace contains:

- `index.php` — landing page to choose between the vulnerable and secure demo versions
- `cab_vulnerable/` — educational version with common vulnerabilities
- `cab_secure/` — secure version with safer coding practices
- `cab_booking.sql` — database schema and sample data

## Features

- User registration and login
- Ride booking and fare handling
- Driver browsing
- Ride history and reviews
- Admin dashboard for bookings and drivers
- Payment flow simulation

## Tech stack

- PHP 8+
- MySQL
- Bootstrap-based UI
- Session-based authentication

## Setup instructions

1. Start Apache and MySQL in XAMPP, WAMP, or Laragon.
2. Create a database named `cab_booking`.
3. Import `cab_booking.sql` into MySQL.
4. Place the project in your web server root (for example, `htdocs/` in XAMPP).
5. Open `http://localhost/SecureRide/` to access the demo selection page.

## How to use

- Open the vulnerable demo from `cab_vulnerable/` to explore insecure behavior.
- Open the secure demo from `cab_secure/` to compare the hardened version.

## Notes

- The vulnerable version is intended only for educational and defensive security demonstrations.
- The secure version is the recommended reference implementation for safe development practices.
