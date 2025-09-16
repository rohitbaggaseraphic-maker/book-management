<?php

/**
 * Simple API Testing Script
 * Run with: php test-api.php
 */

$baseUrl = 'http://localhost:8080';

echo "🚀 Testing Book API\n";
echo "==================\n\n";

// Test 1: Get OAuth2 Token
echo "1. Getting OAuth2 Access Token...\n";
$tokenData = [
    'grant_type' => 'password',
    'client_id' => 'test-client',
    'client_secret' => 'password',
    'username' => 'testuser',
    'password' => 'password'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $tokenResponse = json_decode($response, true);
    $accessToken = $tokenResponse['access_token'] ?? null;
    echo "✅ Token obtained successfully\n";
    echo "Access Token: " . substr($accessToken, 0, 20) . "...\n\n";
} else {
    echo "❌ Failed to get token. HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
    exit(1);
}

// Test 2: Create a Book
echo "2. Creating a book...\n";
$bookData = [
    'bookTitle' => 'The Great Gatsby',
    'bookAuthor' => 'F. Scott Fitzgerald',
    'bookPublishYear' => 1925
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/books');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bookData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 201) {
    $bookResponse = json_decode($response, true);
    $bookId = $bookResponse['bookId'] ?? null;
    echo "✅ Book created successfully\n";
    echo "Book ID: $bookId\n\n";
} else {
    echo "❌ Failed to create book. HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
}

// Test 3: Get All Books
echo "3. Getting all books...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/books');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $books = json_decode($response, true);
    echo "✅ Books retrieved successfully\n";
    echo "Number of books: " . count($books) . "\n\n";
} else {
    echo "❌ Failed to get books. HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
}

// Test 4: Borrow a Book (if we have a book ID)
if (isset($bookId)) {
    echo "4. Borrowing book ID $bookId...\n";
    $borrowData = []; // No userId needed - will come from OAuth token

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/api/books/$bookId/borrow");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($borrowData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        echo "✅ Book borrowed successfully\n\n";
    } else {
        echo "❌ Failed to borrow book. HTTP Code: $httpCode\n";
        echo "Response: $response\n\n";
    }
}

// Test 5: List Borrows for the Book (if we have a book ID)
if (isset($bookId)) {
    echo "5. Getting borrows for book ID $bookId...\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . "/api/books/$bookId/borrows");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $borrows = json_decode($response, true);
        echo "✅ Borrows retrieved successfully\n";
        echo "Number of borrows: " . count($borrows) . "\n\n";
    } else {
        echo "❌ Failed to get borrows. HTTP Code: $httpCode\n";
        echo "Response: $response\n\n";
    }
}

// Test 6: Get Analytics - Latest Borrow Per Book
echo "6. Getting latest borrow per book analytics...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/analytics/latest-borrow-per-book');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Latest borrow per book analytics retrieved successfully\n";
    echo "Response: $response\n\n";
} else {
    echo "❌ Failed to get latest borrow per book analytics. HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
}

// Test 7: Get Analytics - Book Summary
echo "7. Getting book summary analytics...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/analytics/book-summary');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Book summary analytics retrieved successfully\n";
    echo "Response: $response\n\n";
} else {
    echo "❌ Failed to get book summary analytics. HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
}

// Test 8: Get Analytics - Borrow Rank Per User
echo "8. Getting borrow rank per user analytics...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/analytics/borrow-rank-per-user');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Borrow rank per user analytics retrieved successfully\n";
    echo "Response: $response\n\n";
} else {
    echo "❌ Failed to get borrow rank per user analytics. HTTP Code: $httpCode\n";
    echo "Response: $response\n\n";
}

echo "🎉 API Testing Complete!\n";
