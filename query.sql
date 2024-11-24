create database validation_db;ç
use validation_db;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rfc VARCHAR(13) NOT NULL,
    cer_path VARCHAR(255) NOT NULL,
    key_path VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE certificates (
         id INT AUTO_INCREMENT PRIMARY KEY,
         rfc VARCHAR(13) NOT NULL,
         cer_file VARCHAR(255) NOT NULL,
         key_file VARCHAR(255) NOT NULL,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );


