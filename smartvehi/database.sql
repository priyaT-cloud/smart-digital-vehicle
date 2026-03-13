-- ============================================================
-- SmartVehi — Smart Vehicle Service & Digital Parking System
-- BCA 6th Semester | Priya.T (23IABCA120)
-- ============================================================
CREATE DATABASE IF NOT EXISTS smartvehi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartvehi;

-- USERS
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(120) NOT NULL,
    email      VARCHAR(180) NOT NULL UNIQUE,
    phone      VARCHAR(20)  NOT NULL DEFAULT '',
    password   VARCHAR(255) NOT NULL,
    role       ENUM('provider','receiver') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- LISTINGS
CREATE TABLE IF NOT EXISTS listings (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    type             ENUM('parking','washing','rental') NOT NULL,
    name             VARCHAR(200) NOT NULL,
    location         VARCHAR(300) NOT NULL,
    description      TEXT,
    image            VARCHAR(300) DEFAULT NULL,
    -- parking
    price_hour       DECIMAL(10,2) DEFAULT NULL,
    price_day        DECIMAL(10,2) DEFAULT NULL,
    total_slots      INT           DEFAULT NULL,
    vehicle_type     VARCHAR(50)   DEFAULT NULL,
    late_fee_hour    DECIMAL(10,2) DEFAULT 50.00,
    -- washing
    price_basic      DECIMAL(10,2) DEFAULT NULL,
    price_full       DECIMAL(10,2) DEFAULT NULL,
    services_offered TEXT          DEFAULT NULL,
    pickup_drop_fee  DECIMAL(10,2) DEFAULT 100.00,
    -- rental
    rental_type      VARCHAR(100)  DEFAULT NULL,
    vehicle_model    VARCHAR(100)  DEFAULT NULL,
    rent_hour        DECIMAL(10,2) DEFAULT NULL,
    rent_day         DECIMAL(10,2) DEFAULT NULL,
    fuel_type        VARCHAR(50)   DEFAULT NULL,
    late_fee_rental  DECIMAL(10,2) DEFAULT 100.00,
    -- tracking
    is_taken         TINYINT(1)    DEFAULT 0,
    taken_at         DATETIME      DEFAULT NULL,
    return_due       DATETIME      DEFAULT NULL,
    is_active        TINYINT(1)    DEFAULT 1,
    created_at       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- BOOKINGS
CREATE TABLE IF NOT EXISTS bookings (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    listing_id      INT NOT NULL,
    user_id         INT NOT NULL,
    customer_name   VARCHAR(120) NOT NULL,
    customer_phone  VARCHAR(20)  NOT NULL DEFAULT '',
    booking_date    DATE         NOT NULL,
    duration        VARCHAR(50)  NOT NULL,
    notes           TEXT,
    cost            DECIMAL(10,2) DEFAULT 0.00,
    extra_charges   DECIMAL(10,2) DEFAULT 0.00,
    payment_mode    ENUM('credit_card','debit_card','upi','cash') NOT NULL,
    payment_status  ENUM('paid','pending') DEFAULT 'paid',
    upi_app         VARCHAR(30)  DEFAULT NULL,
    pickup_drop     TINYINT(1)   DEFAULT 0,
    status          ENUM('confirmed','taken','completed','cancelled','overdue') DEFAULT 'confirmed',
    receipt_no      VARCHAR(30)  NOT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    listing_id    INT NOT NULL,
    user_id       INT NOT NULL,
    reviewer_name VARCHAR(120) NOT NULL,
    rating        TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment       TEXT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- DEMO ACCOUNTS (password = "password")
INSERT INTO users (full_name, email, phone, password, role) VALUES
('Demo Provider', 'provider@demo.com', '9876543210',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider'),
('Demo Receiver', 'receiver@demo.com', '9123456789',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receiver');

-- DEMO LISTINGS
INSERT INTO listings (user_id,type,name,location,description,price_hour,price_day,total_slots,vehicle_type,late_fee_hour)
VALUES (1,'parking','City Center Parking Zone A','Anna Nagar, Chennai','Covered parking with 24/7 CCTV and security guard. Clean and safe environment.',30.00,200.00,20,'Both',50.00);

INSERT INTO listings (user_id,type,name,location,description,price_basic,price_full,services_offered,pickup_drop_fee)
VALUES (1,'washing','SparkleWash Center','T. Nagar, Chennai','Professional car washing with eco-friendly products. Interior and exterior.',150.00,450.00,'Exterior wash, Interior vacuum, Engine cleaning, Waxing',120.00);

INSERT INTO listings (user_id,type,name,location,description,rental_type,vehicle_model,rent_hour,rent_day,fuel_type,late_fee_rental)
VALUES (1,'rental','City Ride Rentals','Adyar, Chennai','Well-maintained vehicles with GPS tracker. Available 24/7. Free helmet.','2-Wheeler (Bike/Scooter)','Honda Activa 6G',80.00,500.00,'Petrol',100.00);

INSERT INTO reviews (listing_id,user_id,reviewer_name,rating,comment) VALUES
(1,2,'Arjun Kumar',5,'Very clean and safe parking. Great CCTV coverage!'),
(1,2,'Priya Sharma',4,'Good location, affordable pricing. Will use again.'),
(2,2,'Karthik R',5,'My car looks brand new after the full service wash!'),
(3,2,'Sneha M',4,'Smooth ride, well-maintained bike. Punctual return reminder too!');
