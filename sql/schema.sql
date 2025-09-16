CREATE TABLE users (
  userId INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) UNIQUE NOT NULL,
  passwordHash VARCHAR(255) NOT NULL
);

CREATE TABLE books (
  bookId INT AUTO_INCREMENT PRIMARY KEY,
  bookTitle VARCHAR(255) NOT NULL,
  bookAuthor VARCHAR(255),
  bookPublishYear INT,
  FULLTEXT (bookTitle, bookAuthor)
);

CREATE TABLE borrowlog (
  borrowLogId INT AUTO_INCREMENT PRIMARY KEY,
  bookId INT NOT NULL,
  userId INT NOT NULL,
  borrowLogDateTime DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bookId) REFERENCES books(bookId),
  FOREIGN KEY (userId) REFERENCES users(userId)
);

-- OAuth2 Tables
CREATE TABLE oauth_clients (
  client_id VARCHAR(80) NOT NULL PRIMARY KEY,
  client_secret VARCHAR(255),
  redirect_uri VARCHAR(2000),
  grant_types VARCHAR(80),
  scope VARCHAR(4000),
  user_id VARCHAR(80),
  name VARCHAR(255) NOT NULL,
  is_confidential BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE oauth_access_tokens (
  id VARCHAR(100) NOT NULL PRIMARY KEY,
  user_id VARCHAR(80),
  client_id VARCHAR(80) NOT NULL,
  scopes TEXT,
  revoked BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NOT NULL,
  FOREIGN KEY (client_id) REFERENCES oauth_clients(client_id) ON DELETE CASCADE
);

CREATE TABLE oauth_refresh_tokens (
  id VARCHAR(100) NOT NULL PRIMARY KEY,
  access_token_id VARCHAR(100) NOT NULL,
  revoked BOOLEAN DEFAULT FALSE,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens(id) ON DELETE CASCADE
);

-- Insert a default OAuth client for testing
INSERT INTO oauth_clients (client_id, client_secret, name, is_confidential) 
VALUES ('test-client', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test Client', TRUE);
