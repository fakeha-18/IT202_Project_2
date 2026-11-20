-- IT202 Project 2: normalized sample schema and seed data.
-- IMPORT into the NJIT-provided database in phpMyAdmin (do NOT create a separate database).
-- WARNING: Running this script resets these five Project 2 tables and their data.
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS special_instructions;
DROP TABLE IF EXISTS moving_events;
DROP TABLE IF EXISTS client_personal_info;
DROP TABLE IF EXISTS clients;
DROP TABLE IF EXISTS representatives;
SET FOREIGN_KEY_CHECKS=1;
CREATE TABLE representatives (
 rep_id CHAR(4) PRIMARY KEY, first_name VARCHAR(60) NOT NULL, last_name VARCHAR(60) NOT NULL,
 password_hash VARCHAR(255) NOT NULL, phone CHAR(10) NOT NULL, extension VARCHAR(5) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE clients (
 client_id CHAR(4) PRIMARY KEY, first_name VARCHAR(60) NOT NULL, last_name VARCHAR(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE client_personal_info (
 client_id CHAR(4) PRIMARY KEY, street_number VARCHAR(12) NOT NULL, street_name VARCHAR(120) NOT NULL,
 city VARCHAR(80) NOT NULL, state CHAR(2) NOT NULL, zip CHAR(5) NOT NULL, phone CHAR(10) NOT NULL,
 CONSTRAINT fk_person_client FOREIGN KEY(client_id) REFERENCES clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE moving_events (
 moving_id INT PRIMARY KEY, rep_id CHAR(4) NOT NULL, client_id CHAR(4) NOT NULL,
 to_street_number VARCHAR(12) NOT NULL, to_street_name VARCHAR(120) NOT NULL,
 to_city VARCHAR(80) NOT NULL, to_state CHAR(2) NOT NULL, to_zip CHAR(5) NOT NULL,
 moving_date DATE NOT NULL, total_weight DECIMAL(10,2) NOT NULL, miles DECIMAL(10,2) NOT NULL,
 total_cost DECIMAL(12,2) NOT NULL,
 CONSTRAINT fk_move_rep FOREIGN KEY(rep_id) REFERENCES representatives(rep_id),
 CONSTRAINT fk_move_client FOREIGN KEY(client_id) REFERENCES clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE special_instructions (
 moving_id INT PRIMARY KEY, instructions TEXT NOT NULL,
 CONSTRAINT fk_instructions_move FOREIGN KEY(moving_id) REFERENCES moving_events(moving_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO representatives (rep_id, first_name, last_name, password_hash, phone, extension, email) VALUES
 ('1001', 'Sarah', 'Johnson', '$2y$12$N480i3UjlLIWhOZmqeZP7OPMvcPJsGyW0U6OUrIK5oL0JE7vYgSx.', '7325550101', '101', 'sarah@mou.com'),
 ('1002', 'Michael', 'Brown', '$2y$12$GCtfDYYDMNnNTqwhabLeC.IhAvZTg9Vd76vcM4H1gPo5j9Ljej.LS', '7325550102', '102', 'michael@mou.com'),
 ('1003', 'Emily', 'Davis', '$2y$12$MGfMu3vems5KmyKUD3aHZuK.ZmkUHdRgbIykDQIhQQYJJk5Hi7BCa', '7325550103', '103', 'emily@mou.com'),
 ('1004', 'David', 'Wilson', '$2y$12$qmpsW1v/B/LB/FNYGOUMaO6as1n5l1VBvHVcyY03nZV0mdXA54e4K', '7325550104', '104', 'david@mou.com'),
 ('1005', 'Olivia', 'Taylor', '$2y$12$0Y9AM4lwhg7ZUGwYE.b4EOM.lW1ZX706T6acO970pE/qHmlcxP6YO', '7325550105', '105', 'olivia@mou.com'),
 ('1006', 'James', 'Miller', '$2y$12$pvnlgVBQqz9zp0BHfHqCv.AZ52kFw0Gq41WHqZvnxuDNho9j1Qh9S', '7325550106', '106', 'james@mou.com'),
 ('1007', 'Sophia', 'Moore', '$2y$12$/UicqPjTYEHeUdWChWe0YexKtU/XN6tBdSEsZgtrqdx/BnH8YMC8y', '7325550107', '107', 'sophia@mou.com'),
 ('1008', 'Daniel', 'Clark', '$2y$12$f0nWtchwm4jrYyEqf1kJ4.zKc4AiAj/sZBUFRwxoCHAjvNLt9EOLe', '7325550108', '108', 'daniel@mou.com'),
 ('1009', 'Ava', 'White', '$2y$12$9vrptsQnGRmcGDooEdJZ5u2cu0RfmJibfxwR23B/cdMf4gfD/CTMa', '7325550109', '109', 'ava@mou.com'),
 ('1010', 'Noah', 'Martin', '$2y$12$OS26oDlI1Brf2jsI4.QZWeFO0jY4stfZ7zXJl4lW3ospjOo7mvXoS', '7325550110', '110', 'noah@mou.com');
INSERT INTO clients (client_id, first_name, last_name) VALUES
 ('2001', 'Amina', 'Khan'),
 ('2002', 'Liam', 'Brooks'),
 ('2003', 'Maya', 'Patel'),
 ('2004', 'Ethan', 'Reed'),
 ('2005', 'Zara', 'Ali'),
 ('2006', 'Lucas', 'Gray'),
 ('2007', 'Nora', 'Cruz'),
 ('2008', 'Owen', 'Price'),
 ('2009', 'Sara', 'Ahmed'),
 ('2010', 'Leo', 'Bennett');
INSERT INTO client_personal_info (client_id, street_number, street_name, city, state, zip, phone) VALUES
 ('2001', '100', 'Maple Avenue', 'Edison', 'NJ', '08800', '9085551200'),
 ('2002', '108', 'Cedar Street', 'Newark', 'NJ', '08801', '9085551201'),
 ('2003', '116', 'Maple Avenue', 'New Brunswick', 'NJ', '08802', '9085551202'),
 ('2004', '124', 'Cedar Street', 'Jersey City', 'NJ', '08803', '9085551203'),
 ('2005', '132', 'Maple Avenue', 'Hoboken', 'NJ', '08804', '9085551204'),
 ('2006', '140', 'Cedar Street', 'Edison', 'NJ', '08805', '9085551205'),
 ('2007', '148', 'Maple Avenue', 'Newark', 'NJ', '08806', '9085551206'),
 ('2008', '156', 'Cedar Street', 'New Brunswick', 'NJ', '08807', '9085551207'),
 ('2009', '164', 'Maple Avenue', 'Jersey City', 'NJ', '08808', '9085551208'),
 ('2010', '172', 'Cedar Street', 'Hoboken', 'NJ', '08809', '9085551209');
INSERT INTO moving_events (moving_id, rep_id, client_id, to_street_number, to_street_name, to_city, to_state, to_zip, moving_date, total_weight, miles, total_cost) VALUES
 ('600101', '1001', '2001', '300', 'Oak Lane', 'Somerset', 'NJ', '08900', '2027-03-10', '800', '12', '750'),
 ('600102', '1002', '2002', '307', 'Pine Road', 'Piscataway', 'NJ', '08901', '2027-03-11', '910', '16', '925'),
 ('600103', '1003', '2003', '314', 'Oak Lane', 'Princeton', 'NJ', '08902', '2027-03-12', '1020', '20', '1100'),
 ('600104', '1004', '2004', '321', 'Pine Road', 'Montclair', 'NJ', '08903', '2027-03-13', '1130', '24', '1275'),
 ('600105', '1005', '2005', '328', 'Oak Lane', 'Rahway', 'NJ', '08904', '2027-03-14', '1240', '28', '1450'),
 ('600106', '1006', '2006', '335', 'Pine Road', 'Somerset', 'NJ', '08905', '2027-03-15', '1350', '32', '1625'),
 ('600107', '1007', '2007', '342', 'Oak Lane', 'Piscataway', 'NJ', '08906', '2027-03-16', '1460', '36', '1800'),
 ('600108', '1008', '2008', '349', 'Pine Road', 'Princeton', 'NJ', '08907', '2027-03-17', '1570', '40', '1975'),
 ('600109', '1009', '2009', '356', 'Oak Lane', 'Montclair', 'NJ', '08908', '2027-03-18', '1680', '44', '2150'),
 ('600110', '1010', '2010', '363', 'Pine Road', 'Rahway', 'NJ', '08909', '2027-03-19', '1790', '48', '2325');
INSERT INTO special_instructions (moving_id, instructions) VALUES
 ('600101', 'Fragile dishes; use padded boxes.'),
 ('600102', 'Place sofa in living room.'),
 ('600103', 'Label bedroom boxes carefully.'),
 ('600104', 'Use extra tape for artwork.'),
 ('600105', 'Keep electronics upright.'),
 ('600106', 'Bring wardrobe boxes.'),
 ('600107', 'Handle antiques carefully.'),
 ('600108', 'Deliver plants last.'),
 ('600109', 'Use a dolly for heavy furniture.'),
 ('600110', 'Call before arrival.');
