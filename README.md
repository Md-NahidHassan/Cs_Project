## 📌 Project Overview

This project is a secure web-based application developed as part of an academic course.
The main objective of the project is to design and implement a **secure authentication
and authorization system** by following modern web security best practices.

The system allows users to register, login, and access the application securely.
Special emphasis has been given to protecting the system against common security threats
such as brute-force attacks, SQL injection, session hijacking, and unauthorized access.

This project is implemented using **PHP and MySQL**, and multiple security layers have
been added to ensure data protection, user privacy, and system reliability.



# 🔐 Security Features Implemented

# 

The following security mechanisms have been implemented in the project to protect user accounts, authentication processes, sessions, and database operations from common security threats.

&#x20;1. Google reCAPTCHA Integration



Purpose: Prevents automated bots and malicious scripts from performing unauthorized login or authentication attempts.

Implementation: Google reCAPTCHA has been integrated into the authentication process to verify whether the user is a genuine human rather than an automated bot.

Implemented by: Nahid



&#x20;2. Strong Password Policy



Purpose: Prevents users from choosing weak or easily guessable passwords.

Implementation: The system enforces password requirements based on minimum length, character complexity, and other password-strength rules.

Implemented by: Riya



&#x20;3. OTP for Every Login



Purpose: Adds an additional layer of security to the login process.

Implementation: After entering valid login credentials, users are required to provide a One-Time Password (OTP) before gaining access to their account. This helps prevent unauthorized access even if a password is compromised.

Implemented by: Nahid



&#x20;4. Email Verification via OTP



Purpose: Ensures that users have access to the email address provided during registration.

Implementation: A verification OTP is sent to the user's registered email address. The account can only be accessed after successful email verification.

Implemented by: Nahid



&#x20;5. Password Hashing



Purpose: Protects user passwords from being exposed if the database is compromised.

Implementation: Passwords are not stored as plain text. Instead, they are converted into secure hashed values before being stored in the database.

Implemented by: Riya



&#x20;6. Blacklisted IP Protection



Purpose: Blocks access from known or suspicious malicious IP addresses.

Implementation: The system maintains a blacklist of restricted IP addresses and prevents requests from those addresses from accessing protected areas of the application.

Implemented by: Nahid



&#x20;7. Session Auto Logout



Purpose: Prevents unauthorized access when a user leaves the application inactive for a long period.

Implementation: The system automatically terminates the user's session after a predefined period of inactivity, requiring the user to log in again.

Implemented by: Hasnain



&#x20;8. CSRF Protection



Purpose: Protects the application from Cross-Site Request Forgery (CSRF) attacks.

Implementation: CSRF tokens are used to verify that requests to protected actions originate from the legitimate application rather than from a malicious external website.

Implemented by: Tasin



&#x20;9. Rate Limiting for Wrong Password Attempts



Purpose: Prevents brute-force and repeated password-guessing attacks.

Implementation: The system limits the number of unsuccessful login attempts within a specific period. Repeated failed attempts may result in temporary restrictions on further login attempts.

Implemented by: Tasin



&#x20;10. SQL Injection Protection



Purpose: Protects the application and database from SQL Injection attacks.

Implementation: Database queries are handled using prepared statements and parameterized queries instead of directly inserting user-provided input into SQL statements.

Implemented by: Riya



&#x20;11. JavaScript Injection Protection (Input Sanitization)



Purpose: Protects the application against Cross-Site Scripting (XSS) and malicious JavaScript injection.

Implementation: User-provided input is validated and sanitized before being processed or displayed, reducing the risk of malicious scripts being executed in the browser.

Implemented by: Tasin



&#x20;12. Secure Session Termination



Purpose: Ensures that user sessions are properly destroyed after logout.

Implementation: During logout, session data is cleared and the active session is securely terminated to prevent unauthorized reuse of the session.

Implemented by: Hasnain



&#x20;13. Role-Based Authentication



Purpose: Controls access to different features and resources based on the user's assigned role.

Implementation: Users are granted different permissions depending on their roles, such as administrator, staff, or regular user. This ensures that users can only access resources permitted for their role.

Implemented by: Hasnain



&#x20;14. Input Validation Using Regular Expressions



Purpose: Prevents invalid, malformed, or potentially malicious data from being submitted to the application.

Implementation: Regular expressions (Regex) are used to validate specific input formats such as usernames, email addresses, phone numbers, and other user-provided data before processing.

Implemented by: Hasnain

## 🛠️ Technologies Used

The project is developed using the following technologies and tools:

* **PHP (Core PHP)**  
Used for server-side logic, authentication, OTP handling, and security implementation.
* **MySQL**  
Used as the relational database to store user data, OTP records, sessions, and logs.
* **HTML5 \& CSS3**  
Used for structuring and styling the user interface.
* **JavaScript**  
Used for client-side validation and improving user interaction.
* **PHPMailer**  
Used to send OTP emails securely to users.
* **Google reCAPTCHA**  
Integrated to protect the system from automated bot attacks.
* **Apache Server (XAMPP)**  
Used as the local development server environment.

## 🗄️ Database Setup

Follow the steps below to set up the database for this project:

1. Open **phpMyAdmin** from XAMPP control panel.
2. Create a new database named: **farmsystem**
3. Navigate to the **Import** tab.
4. Import the SQL file located at: [**Click**](https://github.com/RakibHossain231/CS-project/tree/main/Database)

This will automatically create all required tables such as user information, OTP storage,
and other related data used for authentication and security.

## 🚀 How to Run the Project

To run this project on your local machine, follow the steps below:

### Method 1: Using XAMPP (Apache & MySQL)
1. Install **XAMPP** on your system.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Copy the project folder and paste it into: \**C:\xampp\htdocs\**
4. Open a web browser and go to: [**Click**](http://localhost/CS-project/login.php)
5. Make sure the database is properly imported before using the application.

### Method 2: Using VS Code Built-in Terminal (PHP Server)
1. Open the **XAMPP Control Panel** and start ONLY **MySQL** (for the database).
2. Open the project folder in **VS Code**.
3. Open the VS Code terminal (`Ctrl` + `` ` ``).
4. Run the following command:
   ```bash
   php -S localhost:8000
   ```
5. Open your web browser and go to: [**http://localhost:8000/login.php**](http://localhost:8000/login.php)
6. Ensure the database is imported correctly before use.

   “We used PHP and MySQL with XAMPP, imported the database using phpMyAdmin, and ran the project through localhost.”



