# PHP Admin Panel

## Overview
This project is a PHP-based admin panel designed for managing users and import receipts. It features a responsive interface built with Bootstrap, allowing administrators to perform various tasks efficiently.

## Features
- **Admin Login/Logout**: Secure login and logout functionalities for administrators.
- **User Management**: 
  - Add new users
  - Edit existing user information
  - Lock and unlock user accounts
  - List all users with management options
- **Import Receipts Management**:
  - List of import receipts
  - Detailed view of each receipt

## Project Structure
```
php-admin-panel
├── assets
│   ├── css
│   │   └── styles.css
│   ├── js
│   │   └── scripts.js
│   └── bootstrap
│       ├── bootstrap.min.css
│       └── bootstrap.min.js
├── includes
│   ├── db.php
│   └── header.php
├── admin
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── users
│   │   ├── add.php
│   │   ├── edit.php
│   │   ├── list.php
│   │   └── lock_unlock.php
│   └── receipts
│       ├── list.php
│       └── view.php
├── index.php
├── .htaccess
└── README.md
```

## Setup Instructions
1. **Clone the Repository**: Download or clone the project to your local server (e.g., XAMPP).
2. **Database Configuration**: 
   - Create a database and import the necessary tables for users and receipts.
   - Update the `includes/db.php` file with your database credentials.
3. **Run the Application**: Access the application via your web browser at `http://localhost/php-admin-panel/index.php`.

## Usage
- Navigate to the login page to access the admin panel.
- Use the dashboard to manage users and view import receipts.
- Follow the prompts to add, edit, or lock/unlock users, and to view detailed receipt information.

## License
This project is open-source and available for modification and distribution under the MIT License.