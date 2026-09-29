CREATE TABLE IF NOT EXISTS site_feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_name VARCHAR(100) NOT NULL,
    rating TINYINT NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Trải nghiệm chung',
    comment TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_feedback_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
