CREATE TABLE IF NOT EXISTS visitor_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(50) NOT NULL,
    nama VARCHAR(255) NOT NULL,
    keperluan ENUM('membaca', 'meminjam', 'mengembalikan', 'lainnya') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
