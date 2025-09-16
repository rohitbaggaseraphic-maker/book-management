# Book API Setup Guide

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- A web server (Apache/Nginx) or PHP built-in server

## Step 1: Install Dependencies

```bash
composer install
```

## Step 2: Database Setup

1. Create a MySQL database:

```sql
CREATE DATABASE bookdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update your `.env` file with your database credentials:

```env
DB_HOST=127.0.0.1
DB_NAME=bookdb
DB_USER=your_username
DB_PASS=your_password
```

3. Import the database schema:

```bash
mysql -u your_username -p bookdb < sql/schema.sql
```

## Step 3: Create Required Directories

```bash
mkdir -p logs
chmod 755 logs
```

## Step 4: Start the Development Server

```bash
php -S localhost:8000 -t public
```

Your API will be available at: `http://localhost:8000`

## Step 5: Test the API

### 1. Get OAuth2 Access Token

```bash
curl -X POST http://localhost:8000/api/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password&client_id=test-client&client_secret=password&username=testuser&password=testpass"
```

### 2. Create a Book

```bash
curl -X POST http://localhost:8000/api/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"bookTitle":"The Great Gatsby","bookAuthor":"F. Scott Fitzgerald","bookPublishYear":1925}'
```

### 3. Get All Books

```bash
curl -X GET http://localhost:8000/api/books \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### 4. Borrow a Book

```bash
curl -X POST http://localhost:8000/api/books/1/borrow \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{"userId":1}'
```

### 5. Get Analytics

```bash
curl -X GET http://localhost:8000/api/analytics/latest-borrow-per-book \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

```bash
curl -X GET http://localhost:8000/api/analytics/book-summary \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

```bash
curl -X GET http://localhost:8000/api/analytics/borrow-rank-per-user \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## Troubleshooting

1. **Database Connection Issues**: Check your `.env` file and ensure MySQL is running
2. **Permission Issues**: Ensure the `logs` directory is writable
3. **OAuth2 Issues**: Make sure the `oauth-private.key` file exists and is readable
4. **Autoloading Issues**: Run `composer dump-autoload` if classes aren't found

```

```
