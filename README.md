# 💕 LoveConnect - Online Dating Application

A modern, responsive online dating application built with PHP and JavaScript.

## Features

- **User Registration & Authentication**: Secure user registration and login system
- **Profile Management**: Users can create and edit their profiles with personal information
- **Discovery System**: Browse and discover potential matches with like/reject functionality
- **Matching Algorithm**: Mutual likes create matches between users
- **Real-time Messaging**: Chat with your matches through an intuitive messaging interface
- **Responsive Design**: Works perfectly on desktop and mobile devices

## Technologies Used

- **Backend**: PHP 7.4+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Database**: MySQL with PDO
- **Security**: Password hashing, SQL injection prevention, XSS protection

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/goneglobal/onlinedating.git
   cd onlinedating
   ```

2. **Database Setup**
   - Create a MySQL database named `onlinedating`
   - Import the database schema:
   ```bash
   mysql -u your_username -p onlinedating < database.sql
   ```

3. **Configuration**
   - Edit `config.php` to match your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'onlinedating');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Web Server Setup**
   - Point your web server document root to the project directory
   - Ensure PHP and MySQL are properly configured
   - Make sure `mod_rewrite` is enabled (for Apache)

5. **Permissions**
   ```bash
   chmod 755 uploads/  # If you plan to add file upload functionality
   ```

## Usage

1. **Access the Application**
   - Navigate to your domain in a web browser
   - You'll be redirected to the login page

2. **User Registration**
   - Click on the "Register" tab
   - Fill in all required information
   - Create your account

3. **Profile Setup**
   - After registration, complete your profile
   - Add a bio and location information

4. **Find Matches**
   - Browse potential matches on the home page
   - Like or reject profiles
   - Mutual likes create matches

5. **Messaging**
   - View your matches on the matches page
   - Start conversations with your matches
   - Exchange messages in real-time

## File Structure

```
onlinedating/
├── README.md           # Project documentation
├── config.php          # Database configuration and helper functions
├── database.sql        # Database schema and sample data
├── index.php          # Main homepage with discovery
├── login.php          # User authentication
├── logout.php         # Session termination
├── profile.php        # Profile management
├── matches.php        # View matches and pending likes
├── messages.php       # Messaging interface
├── actions.php        # AJAX endpoint for likes/rejects
├── style.css          # Application styling
├── script.js          # JavaScript functionality
└── .gitignore         # Git ignore rules
```

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session management for authentication
- CSRF protection on forms

## Sample Users

The database comes with sample users for testing:
- **john_doe** (john@example.com)
- **jane_smith** (jane@example.com)  
- **alex_wilson** (alex@example.com)

All sample passwords are hashed examples - you'll need to register new users.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support, please open an issue on GitHub or contact the development team.
