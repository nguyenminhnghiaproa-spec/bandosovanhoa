CREATE TABLE IF NOT EXISTS location_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    location_id INT NOT NULL,
    reviewer_name VARCHAR(100) NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT NOT NULL,
    review_image VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_review_location (location_id),
    INDEX idx_review_status (status),
    CONSTRAINT fk_location_reviews_location FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nếu bảng location_reviews đã tồn tại, chạy riêng câu dưới trong phpMyAdmin:
-- ALTER TABLE location_reviews ADD COLUMN review_image VARCHAR(255) NULL AFTER comment;
