-- Create database if not exists
CREATE DATABASE IF NOT EXISTS savion_vechicle_booking;

-- Use the database
USE savion_vechicle_booking;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('customer', 'service_provider', 'admin') NOT NULL DEFAULT 'customer',
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    vehicle_name VARCHAR(100) NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    license_plate VARCHAR(20) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    capacity INT NOT NULL,
    rate_per_hour DECIMAL(10, 2) NOT NULL,
    availability BOOLEAN DEFAULT TRUE,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    dropoff_location VARCHAR(255) NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(vehicle_id) ON DELETE CASCADE
);

-- Create ratings table
CREATE TABLE IF NOT EXISTS ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    rated_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (rated_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create messages table
CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    booking_id INT,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE SET NULL
);

-- Create payments table
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    related_id INT,
    notification_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Create reports table for cumulative reporting
CREATE TABLE IF NOT EXISTS reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    total_requests INT DEFAULT 0,
    accepted_requests INT DEFAULT 0,
    rejected_requests INT DEFAULT 0,
    completed_bookings INT DEFAULT 0,
    cancelled_bookings INT DEFAULT 0,
    total_earnings DECIMAL(10, 2) DEFAULT 0.00,
    average_rating DECIMAL(3, 2) DEFAULT 0.00,
    report_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert dummy service providers
INSERT INTO users (username, email, password, user_type) VALUES
('provider1', 'provider1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'service_provider'),
('provider2', 'provider2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'service_provider'),
('provider3', 'provider3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'service_provider');

-- Insert dummy vehicles with different locations (for map markers)
INSERT INTO vehicles (provider_id, vehicle_name, vehicle_type, license_plate, model, year, capacity, rate_per_hour, latitude, longitude, image) VALUES
(1, 'Toyota Camry', 'Sedan', 'ABC123', 'Camry', 2020, 4, 25.00, 40.7128, -74.0060, 'assets/images/vehicles/camry.jpg'),
(1, 'Honda Civic', 'Sedan', 'DEF456', 'Civic', 2019, 4, 22.00, 40.7282, -73.9942, 'assets/images/vehicles/civic.jpg'),
(2, 'Ford F-150', 'Truck', 'GHI789', 'F-150', 2021, 5, 35.00, 40.7112, -74.0123, 'assets/images/vehicles/f150.jpg'),
(2, 'Chevrolet Suburban', 'SUV', 'JKL012', 'Suburban', 2018, 7, 40.00, 40.7200, -74.0148, 'assets/images/vehicles/suburban.jpg'),
(3, 'BMW 5 Series', 'Luxury', 'MNO345', '5 Series', 2022, 5, 50.00, 40.7300, -73.9950, 'assets/images/vehicles/bmw5.jpg'),
(3, 'Mercedes-Benz E-Class', 'Luxury', 'PQR678', 'E-Class', 2021, 5, 55.00, 40.7350, -74.0050, 'assets/images/vehicles/eclass.jpg');

-- Insert dummy customers
INSERT INTO users (username, email, password, user_type) VALUES
('customer1', 'customer1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('customer2', 'customer2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Insert dummy bookings
INSERT INTO bookings (customer_id, vehicle_id, start_time, end_time, pickup_location, dropoff_location, status, total_amount) VALUES
(4, 1, '2023-06-15 10:00:00', '2023-06-15 14:00:00', '123 Main St, New York, NY', '456 Park Ave, New York, NY', 'completed', 100.00),
(4, 3, '2023-06-20 09:00:00', '2023-06-20 12:00:00', '789 Broadway, New York, NY', '101 5th Ave, New York, NY', 'accepted', 105.00),
(5, 2, '2023-06-25 13:00:00', '2023-06-25 17:00:00', '202 Wall St, New York, NY', '303 Madison Ave, New York, NY', 'pending', 88.00),
(5, 4, '2023-06-30 15:00:00', '2023-06-30 18:00:00', '404 Lexington Ave, New York, NY', '505 3rd Ave, New York, NY', 'rejected', 120.00);

-- Insert dummy ratings
INSERT INTO ratings (booking_id, user_id, rated_id, rating, comment) VALUES
(1, 4, 1, 5, 'Excellent service and very clean vehicle!'),
(1, 1, 4, 4, 'Good customer, returned the vehicle in good condition.');

-- Insert dummy reports for providers
INSERT INTO reports (provider_id, total_requests, accepted_requests, rejected_requests, completed_bookings, cancelled_bookings, total_earnings, average_rating, report_date) VALUES
(1, 10, 7, 2, 5, 1, 450.00, 4.5, '2023-06-30'),
(2, 8, 5, 1, 4, 2, 380.00, 4.2, '2023-06-30'),
(3, 6, 4, 1, 3, 1, 320.00, 4.0, '2023-06-30');

-- Add reset token columns to users table
-- Remove this section as these columns are already defined in the users table
-- ALTER TABLE users 
-- ADD COLUMN reset_token VARCHAR(255) NULL,
-- ADD COLUMN reset_token_expires DATETIME NULL;