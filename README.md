# README.md

This is the comprehensive documentation for the **Smart Choice Academy LMS-PRO** platform.

---

## 🚀 Project Overview

**LMS-PRO** is a professional-grade Learning Management System developed for **Smart Choice Academy**. The platform facilitates an end-to-end educational workflow, from secure student registration and email verification to course completion and automated professional certification.

## 🛠️ Key Features

* **Role-Based Access Control (RBAC):** Distinct dashboards and permissions for **Students**, **Teachers**, and **Admins**.
* **Security-First Authentication:**
* Bcrypt password hashing.
* Mandatory **Email Verification** via SMTP before account activation.
* Account "Disabled" status handling for administrative control.


* **Academic Content Management:** Hierarchical structure supporting Courses → Modules → Lessons.
* **Professional Certification:**
* Automated PDF generation using FPDF.
* Dynamic branding with Academy Logo and Director’s Signature.
* **QR Code Integration:** Every certificate includes a unique QR code for instant digital verification.
* Unique Verification IDs (e.g., `SCA-22D974A6D8`).



## 📁 Project Architecture

```text
/LMS-Project
│
├── config.php             # Database connection & global constants
├── User.php               # User Logic (Login, Register, verification)
├── Enrollment.php         # Student-Course relationship logic
├── login.php              # Modern login interface with Lucide icons
├── register.php           # Registration logic with PHPMailer integration
├── verify_email.php       # Email token validation landing page
├── generate_pdf.php       # The certificate generation engine (FPDF)
├── /vendor                # Composer dependencies (PHPMailer, etc.)
├── logo.png               # Academy branding asset
├── signature.png          # Director's signature asset
└── style.css              # Custom UI styling

```

## 🏗️ Database Design

The system relies on a relational MySQL database to maintain data integrity and track student progress.

**Core Tables:**

* **`users`**: Stores credentials, roles, and verification status.
* **`courses`**: Metadata for available subjects.
* **`modules` & `lessons**`: The structural content of each course.
* **`enrollments`**: Tracks which students are signed up for which courses.
* **`quiz_results`**: Validates eligibility for certificate generation.

## 🚀 Installation & Setup

### 1. Requirements

* PHP 7.4 or higher
* MySQL 5.7+
* Composer (for PHPMailer)

### 2. Database Setup

1. Create a database named `lms_db`.
2. Import the SQL schema provided in the documentation to create the necessary tables.

### 3. SMTP Configuration

Update the following credentials in `register.php` to enable email verification:

* **Host:** `mail.softzila.com`
* **Port:** `465` (SSL/TLS)
* **Username:** `noreply@softzila.com`

### 4. Composer Installation

Run the following command in your terminal to install PHPMailer:

```bash
composer require phpmailer/phpmailer

```

## 🔐 Security Standards

The platform implements modern security practices to protect user data:

1. **SQL Injection Prevention:** All database interactions utilize PHP Prepared Statements.
2. **Cross-Site Scripting (XSS) Protection:** All user-generated content is sanitized using `htmlspecialchars()` and `strip_tags()`.
3. **Session Security:** Uses `session_regenerate_id(true)` to prevent session fixation attacks.

## 📜 Professional Certificate Engine

The `generate_pdf.php` script is the heart of the Academy's branding. It uses the FPDF library to render a landscape A4 diploma.

**Features of the Diploma:**

* **Vector Borders:** High-quality luxury frame.
* **Signature Placement:** Uses `$pdf->Image('signature.png', ...)` to place the director's signature precisely over the designation line.
* **Verification QR:** Generates a real-time QR code via the QuickChart API pointing to the academy's verification portal.

---

*Developed for Smart Choice Academy by Softzila.*
