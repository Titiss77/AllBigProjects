CREATE DATABASE IF NOT EXISTS calendrier CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE calendrier;

CREATE TABLE IF NOT EXISTS availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_hour INT NOT NULL,
    end_hour INT NOT NULL
);

CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day VARCHAR(20) NOT NULL,
    start_time VARCHAR(5) NOT NULL,
    title VARCHAR(100) NOT NULL,
    duration INT NOT NULL,
    color VARCHAR(7) NOT NULL
);

-- Insertion de tes plages de disponibilité
INSERT INTO availability (start_hour, end_hour) VALUES 
(9, 12), 
(13, 20);

-- Insertion de tes entraînements
INSERT INTO activities (day, start_time, title, duration, color) VALUES
('Lundi', '10:00', 'Salle de sport', 90, '#ff9500'),
('Lundi', '18:30', 'Natation', 90, '#007aff'),
('Mercredi', '10:00', 'Course à pied', 45, '#34c759'),
('Mercredi', '18:30', 'Natation', 90, '#007aff'),
('Jeudi', '14:00', 'Salle de sport', 90, '#ff9500'),
('Vendredi', '19:00', 'Natation', 90, '#007aff'),
('Samedi', '10:00', 'Salle de sport', 90, '#ff9500'),
('Samedi', '13:30', 'Natation', 120, '#007aff'),
('Dimanche', '10:00', 'Course à pied', 60, '#34c759');