USE bookly;
INSERT INTO users (username, email, password, is_admin)
VALUES
('admin', 'admin@bookly.com', SHA2('admin123', 256), TRUE),
('alice', 'alice@example.com', SHA2('alice123', 256), FALSE),
('bob', 'bob@example.com', SHA2('bob123', 256), FALSE);

INSERT INTO categories (name, description)
VALUES
('Science Fiction', 'Books set in futuristic worlds or space.'),
('Fantasy', 'Magical or supernatural stories in imaginary worlds.'),
('Romance', 'Love stories and relationships.'),
('Technology', 'Books about software, hardware, and IT topics.');

INSERT INTO books (category_id, title, author, description, price, cover_image)
VALUES
(1, 'Dune', 'Frank Herbert', 'Epic science fiction novel.', 12.99, 'dune.jpg'),
(2, 'Harry Potter and the Sorcerer\'s Stone', 'J.K. Rowling', 'A young wizard\'s journey begins.', 9.99, 'hp1.jpg'),
(3, 'Pride and Prejudice', 'Jane Austen', 'Classic romance novel.', 7.49, 'pride.jpg'),
(4, 'Clean Code', 'Robert C. Martin', 'A handbook of agile software craftsmanship.', 25.00, 'cleancode.jpg');

INSERT INTO orders (user_id, total_amount)
VALUES
(2, 22.98),
(3, 25.00);

INSERT INTO order_items (order_id, book_id, quantity, price)
VALUES
(1, 1, 1, 12.99),
(1, 3, 1, 9.99),
(2, 4, 1, 25.00);

INSERT INTO reviews (user_id, book_id, rating, comment)
VALUES
(2, 1, 5, 'An absolute masterpiece!'),
(3, 4, 4, 'Very informative, well-written.'),
(2, 2, 5, 'Magical and nostalgic!');
