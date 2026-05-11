CREATE DATABASE IF NOT EXISTS car_sales DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE car_sales;

-- ====================== 创建 users 表 ======================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id VARCHAR(20) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入用户数据
INSERT INTO users (id, username, password, name, address, phone, email)
VALUES
('u1', 'alice', 'password1', 'Alice Johnson', '123 Main St, Los Angeles, CA', '212-555-0101', 'alice@example.com'),
('u2', 'bob', 'password2', 'Bob Smith', '456 Oak Ave, Miami, FL', '310-555-0202', 'bob@example.com'),
('u3', 'charlie', 'password3', 'Charlie Lee', '789 Pine Rd, Chicago, IL', '312-555-0303', 'charlie@example.com');

-- ====================== 创建 cars 表 ======================
DROP TABLE IF EXISTS cars;
CREATE TABLE cars (
    id VARCHAR(20) PRIMARY KEY,
    sellerId VARCHAR(20) NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    mileage INT NOT NULL,
    color VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    transmission VARCHAR(50) NOT NULL,
    fuelType VARCHAR(50) NOT NULL,
    engine VARCHAR(100) NOT NULL,
    drivetrain VARCHAR(20) NOT NULL,
    vin VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    image VARCHAR(255) NOT NULL,
    imageLarge VARCHAR(255) NOT NULL,
    -- 外键关联用户
    FOREIGN KEY (sellerId) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入车辆数据
INSERT INTO cars (id, sellerId, brand, model, year, price, mileage, color, location, transmission, fuelType, engine, drivetrain, vin, description, phone, image, imageLarge)
VALUES
('c1', 'u1', 'BMW', 'M4 Competition', 2022, 65000.00, 12500, 'Black Sapphire', 'Los Angeles, CA', 'Automatic', 'Gasoline', '3.0L Twin-Turbo I6', 'RWD', 'WBS43AZ0XNCJ12345', 'Immaculate condition. Low miles, ceramic coating, full service history.', '212-555-0101', 'https://placehold.co/600x400/1a1a1a/ffffff?text=BMW+M4', 'https://placehold.co/800x500/1a1a1a/ffffff?text=BMW+M4+Competition'),
('c2', 'u1', 'Mercedes-Benz', 'C300', 2020, 35000.00, 28000, 'Polar White', 'New York, NY', 'Automatic', 'Gasoline', '2.0L Turbo I4', 'RWD', 'WDDWF8DB2LR567890', 'Elegant and refined. Premium package, panoramic roof.', '212-555-0101', 'https://placehold.co/600x400/1a1a1a/ffffff?text=MERCEDES+C300', 'https://placehold.co/800x500/1a1a1a/ffffff?text=MERCEDES+C300'),
('c3', 'u2', 'Audi', 'RS5 Sportback', 2023, 72000.00, 5200, 'Nardo Gray', 'Miami, FL', 'Automatic', 'Gasoline', '2.9L Twin-Turbo V6', 'AWD', 'WUAAWDF57PN901234', 'Nearly new, showroom condition. Sport exhaust, dynamic package.', '310-555-0202', 'https://placehold.co/600x400/1a1a1a/ffffff?text=AUDI+RS5', 'https://placehold.co/800x500/1a1a1a/ffffff?text=AUDI+RS5+Sportback'),
('c4', 'u2', 'Porsche', '911 Carrera', 2019, 85000.00, 18000, 'GT Silver', 'Chicago, IL', 'PDK Automatic', 'Gasoline', '3.0L Twin-Turbo Flat-6', 'RWD', 'WP0AA2A99KS345678', 'Iconic silhouette, timeless performance. Sport Chrono, adaptive suspension.', '310-555-0202', 'https://placehold.co/600x400/1a1a1a/ffffff?text=PORSCHE+911', 'https://placehold.co/800x500/1a1a1a/ffffff?text=PORSCHE+911+Carrera'),
('c5', 'u3', 'Tesla', 'Model 3 Performance', 2021, 42000.00, 22000, 'Pearl White', 'San Francisco, CA', 'Single-Speed', 'Electric', 'Dual Motor AWD', 'AWD', '5YJ3E1EC8MF987654', 'Instant torque, zero emissions. Full self-driving capability included.', '312-555-0303', 'https://placehold.co/600x400/1a1a1a/ffffff?text=TESLA+MODEL+3', 'https://placehold.co/800x500/1a1a1a/ffffff?text=TESLA+MODEL+3+Perf'),
('c6', 'u3', 'Range Rover', 'Sport HSE', 2022, 55000.00, 15800, 'Santorini Black', 'Dallas, TX', 'Automatic', 'Gasoline', '3.0L Turbo I6 MHEV', 'AWD', 'SALWV2SV4NA456789', 'Commanding presence with refined luxury. Air suspension, Meridian audio.', '312-555-0303', 'https://placehold.co/600x400/1a1a1a/ffffff?text=RANGE+ROVER+SPORT', 'https://placehold.co/800x500/1a1a1a/ffffff?text=RANGE+ROVER+SPORT'),
('c7', 'u1', 'BMW', 'X5 xDrive40i', 2020, 48000.00, 32000, 'Dark Graphite', 'Seattle, WA', 'Automatic', 'Gasoline', '3.0L Turbo I6', 'AWD', '5UXCR6C05L9C23456', 'Versatile luxury SUV. M Sport package, panoramic roof, heated seats.', '212-555-0101', 'https://placehold.co/600x400/1a1a1a/ffffff?text=BMW+X5', 'https://placehold.co/800x500/1a1a1a/ffffff?text=BMW+X5+xDrive40i'),
('c8', 'u2', 'Mercedes-Benz', 'GLE 450', 2023, 62000.00, 8100, 'Obsidian Black', 'Phoenix, AZ', 'Automatic', 'Gasoline', '3.0L Turbo I6 EQ Boost', 'AWD', '4JGFB5KB7PA789012', 'Modern luxury redefined. MBUX, driver assistance, 4MATIC.', '310-555-0202', 'https://placehold.co/600x400/1a1a1a/ffffff?text=MERCEDES+GLE', 'https://placehold.co/800x500/1a1a1a/ffffff?text=MERCEDES+GLE+450');

ALTER TABLE cars DROP vin;
ALTER TABLE cars DROP engine;