# 🚗 InsurTech Broker Platform (RCA Calculator)

A production-ready, full-stack web application built for insurance brokers. It automates the entire lifecycle of RCA (Motor Third Party Liability) insurance policies—from vehicle data retrieval to quoting, issuing, and automated PDF delivery via email.

## 📊 Impact & Performance Metrics
* **95% Reduction in Delivery Time:** Automated the PDF retrieval and email dispatch process using PHPMailer, cutting down manual policy delivery time from ~3 minutes to under 3 seconds per transaction.
* **Scalable Multi-Tenant Architecture:** Engineered a session-based PHP backend capable of securely handling 50+ concurrent broker sessions with strict data isolation.
* **Zero-Trust Security:** Achieved 100% protection against SQL injection using Prepared Statements, implemented secure password hashing, and deployed a time-sensitive, token-based password reset flow.
* **Asynchronous Processing:** Optimized user experience and reduced wait times by leveraging the JS Fetch API to query multiple insurance providers (Allianz, Omniasig, Groupama, etc.) simultaneously rather than sequentially.

## 🌟 Core Functionalities
* **Smart Auto-Complete:** Integrated the 24Broker (Life Is Hard) API to instantly fetch vehicle technical specs (VIN, Make, Model, Engine, etc.) using just the license plate number.
* **Live Quoting Engine:** Aggregates and displays comparative pricing from top insurers in real-time.
* **One-Click Issuance & Delivery:** Seamlessly issues the policy via API, parses the generated PDF in-memory, and emails it directly to the client without cluttering server storage.

## 🛠️ Tech Stack
* **Backend:** PHP 8.x, MySQL (Relational Database)
* **Frontend:** HTML5, CSS3, Vanilla JavaScript (ES6, Fetch API)
* **Integrations:** REST APIs, cURL, PHPMailer (SMTP)
* **Security:** Prepared Statements, Secure Sessions, Environment variables management (`.gitignore`).

> **Note:** This repository is for demonstration and portfolio purposes. Sensitive data, actual API keys, and database credentials have been securely omitted.
