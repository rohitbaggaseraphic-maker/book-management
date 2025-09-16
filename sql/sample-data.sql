-- Sample data for testing the Book API

-- Insert test users
INSERT INTO users (username, passwordHash) VALUES 
('testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Insert sample books
INSERT INTO books (bookTitle, bookAuthor, bookPublishYear) VALUES
('To Kill a Mockingbird', 'Harper Lee', 1960),
('1984', 'George Orwell', 1949),
('Pride and Prejudice', 'Jane Austen', 1813),
('The Great Gatsby', 'F. Scott Fitzgerald', 1925),
('Harry Potter and the Philosopher\'s Stone', 'J.K. Rowling', 1997),
('The Catcher in the Rye', 'J.D. Salinger', 1951),
('Lord of the Flies', 'William Golding', 1954),
('The Hobbit', 'J.R.R. Tolkien', 1937);

-- Insert sample borrow logs
INSERT INTO borrowlog (userId, bookId, borrowLogDateTime) VALUES
(1, 1, '2024-01-15 10:30:00'),
(2, 1, '2024-01-20 14:15:00'),
(1, 2, '2024-01-25 09:45:00'),
(3, 3, '2024-02-01 16:20:00'),
(2, 4, '2024-02-05 11:10:00'),
(1, 5, '2024-02-10 13:30:00'),
(3, 2, '2024-02-15 15:45:00'),
(2, 6, '2024-02-20 10:00:00');