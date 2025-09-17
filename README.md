# Book API

A RESTful API for managing books and borrowing records using PHP Slim Framework with OAuth2 authentication.

## Features

- Book management (CRUD operations)
- User authentication with OAuth2
- Borrowing system with logging
- Analytics and reporting
- Request logging middleware

## Tech Stack

- PHP 8.0+
- Slim Framework 4
- PHP-DI for dependency injection
- League OAuth2 Server
- MySQL database
- Monolog for logging

## Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE bookdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Update .env with your database credentials
# Then import schema
mysql -u root -p bookdb < sql/schema.sql

# Optional: Import sample data
mysql -u root -p bookdb < sql/sample-data.sql
```

### 3. Start Development Server

```bash
php -S localhost:8000 -t public
```

### 4. Test the API

```bash
php test-api.php
```

## API Endpoints

### Authentication

- `POST /api/token` - Get OAuth2 access token

### Books

- `GET /api/books` - Get all books
- `POST /api/books` - Create a new book
- `POST /api/books/{id}/borrow` - Borrow a book

### Analytics

- `GET api/analytics/latest-borrow-per-book` - Latest borrow per book
- `GET api/analytics/borrow-rank-per-user` - User borrowing rankings
- `GET api/analytics/book-summary` - Book statistics

## Testing

### Manual Testing with cURL

1. **Get Access Token:**

```bash
curl -X POST http://localhost:8000/api/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password&client_id=test-client&client_secret=password&username=testuser&password=password"
```

2. **Create a Book:**

```bash
curl -X POST http://localhost:8000/api/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"bookTitle":"The Great Gatsby","bookAuthor":"F. Scott Fitzgerald","bookPublishYear":1925}'
```

3. **Get All Books:**

```bash
curl -X GET http://localhost:8000/api/books \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### Automated Testing

Run the test script:

```bash
php test-api.php
```

## Configuration

### Environment Variables (.env)

```env
DB_HOST=127.0.0.1
DB_NAME=bookdb
DB_USER=root
DB_PASS=your_password

OAUTH_PRIVATE_KEY_PATH=oauth-private.key
OAUTH_ENCRYPTION_KEY=your_encryption_key

APP_ENV=development
LOG_LEVEL=INFO
```

### Default OAuth2 Client

- Client ID: `test-client`
- Client Secret: `password`

### Test User Credentials

- Username: `testuser`
- Password: `password`

## Project Structure

```
├── public/
│   └── index.php          # Application entry point
├── src/
│   └── Controllers/       # API controllers
├── Services/              # Business logic
├── Repositories/          # Data access layer
├── Middleware/            # Custom middleware
├── sql/
│   ├── schema.sql         # Database schema
│   └── sample-data.sql    # Sample data
├── logs/                  # Application logs
├── .env                   # Environment configuration
├── composer.json          # Dependencies
└── php-di-config.php      # Dependency injection config
```

## Troubleshooting

1. **Database Connection Issues**: Check your `.env` file and ensure MySQL is running
2. **Permission Issues**: Ensure the `logs` directory is writable (`chmod 755 logs`)
3. **OAuth2 Issues**: Make sure the `oauth-private.key` file exists and is readable
4. **Autoloading Issues**: Run `composer dump-autoload` if classes aren't found
5. **Port Already in Use**: Change the port: `php -S localhost:8001 -t public`
