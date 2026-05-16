-- ============================================
-- WILDLIFE CONSERVATION MONITORING SYSTEM
-- Database Schema, Data, Queries, Functions & Triggers
-- PostgreSQL / Supabase
-- ============================================
-- Project: CMPE344 Database Management Systems
-- Date: May 2026
-- ============================================

-- ============================================
-- 1. DATA DEFINITION LANGUAGE (DDL)
-- ============================================

-- Enable UUID extension (for future Supabase Auth)
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Table 1: Roles
CREATE TABLE roles (
    role_id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- Table 2: Users
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role_id INTEGER NOT NULL REFERENCES roles(role_id) ON DELETE CASCADE
);

-- Table 3: Species
CREATE TABLE species (
    species_id SERIAL PRIMARY KEY,
    common_name VARCHAR(100) NOT NULL,
    scientific_name VARCHAR(150) NOT NULL UNIQUE,
    conservation_status VARCHAR(50) NOT NULL
);

-- Table 4: Animals
CREATE TABLE animals (
    animal_id SERIAL PRIMARY KEY,
    species_id INTEGER NOT NULL REFERENCES species(species_id) ON DELETE CASCADE,
    gender VARCHAR(10),
    date_of_birth DATE,
    health_status VARCHAR(50)
);

-- Table 5: Protected Areas
CREATE TABLE protected_areas (
    area_id SERIAL PRIMARY KEY,
    area_name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    area_type VARCHAR(50)
);

-- Table 6: Rangers
CREATE TABLE rangers (
    ranger_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL UNIQUE REFERENCES users(user_id) ON DELETE CASCADE,
    area_id INTEGER NOT NULL REFERENCES protected_areas(area_id),
    phone VARCHAR(20)
);

-- Table 7: Observations
CREATE TABLE observations (
    observation_id SERIAL PRIMARY KEY,
    animal_id INTEGER NOT NULL REFERENCES animals(animal_id) ON DELETE CASCADE,
    ranger_id INTEGER NOT NULL REFERENCES rangers(ranger_id),
    area_id INTEGER NOT NULL REFERENCES protected_areas(area_id),
    observation_date DATE NOT NULL,
    behavior_notes TEXT,
    health_condition VARCHAR(100)
);

-- Table 8: Observation Log (Audit Trail)
CREATE TABLE observation_log (
    log_id SERIAL PRIMARY KEY,
    observation_id INTEGER REFERENCES observations(observation_id),
    action_type VARCHAR(50),
    action_date TIMESTAMP DEFAULT NOW()
);


-- ============================================
-- 2. DATA MANIPULATION LANGUAGE (DML)
-- ============================================

-- Insert Roles
INSERT INTO roles (role_name) VALUES 
('Admin'),
('Ranger'),
('Researcher');

-- Insert Users
INSERT INTO users (username, password, full_name, email, role_id) VALUES
('alanad', 'alanad123', 'Ahmad Alanad', 'alanad@wildlife.org', 1),
('abdullah', 'abdullah123', 'Abdullah Alsawadi', 'abdul@wildlife.org', 2),
('Imane', 'Imane123', 'Imane Ennassiri', 'iman@wildlife.org', 2),
('Berqane', 'Berqane123', 'Berqane', 'berqane@wildlife.org', 3);

-- Insert Species
INSERT INTO species (common_name, scientific_name, conservation_status) VALUES
('Karpas Wild Donkey', 'Equus asinus', 'Endemic'),
('Green Turtle', 'Chelonia mydas', 'Protected'),
('Long-eared Hedgehog', 'Hemiechinus auritus', 'Endangered'),
('Snow Leopard', 'Panthera uncia', 'Vulnerable');

-- Insert Animals
INSERT INTO animals (species_id, gender, date_of_birth, health_status) VALUES
(1, 'Male', '2016-04-12', 'Healthy'),
(1, 'Female', '2018-08-20', 'Injured'),
(2, 'Female', '2019-02-15', 'Healthy'),
(3, 'Male', '2020-11-05', 'Healthy'),
(4, 'Female', '2017-06-30', 'Under Observation'),
(1, 'Male', '2026-05-15', 'Injured'),
(2, 'Male', '2026-05-15', 'Injured');

-- Insert Protected Areas
INSERT INTO protected_areas (area_name, location, area_type) VALUES
('Karpaz zone', 'Eastern Region', 'Wildlife Reserve'),
('Magusa Park', 'Northern Region', 'National Park'),
('Mountain Safe Zone', 'Western Region', 'Protected Zone');

-- Insert Rangers
INSERT INTO rangers (user_id, area_id, phone) VALUES
(2, 1, '0551112233'),
(3, 2, '0554445566');

-- Insert Observations
INSERT INTO observations (animal_id, ranger_id, area_id, observation_date, behavior_notes, health_condition) VALUES
(1, 1, 1, '2026-04-01', 'Moving slowly, possible leg injury', 'Injured'),
(2, 1, 1, '2026-04-03', 'Seen near mountain rocks', 'Healthy'),
(3, 2, 2, '2026-04-05', 'Observed near water source', 'Healthy'),
(4, 2, 2, '2026-04-07', 'Eating calmly', 'Healthy'),
(5, 1, 3, '2026-04-10', 'Active hunting behavior observed', 'Under Observation');

-- Insert Audit Log Sample
INSERT INTO observation_log (observation_id, action_type, action_date) VALUES
(6, 'DELETE', '2026-05-06 14:55:03');


-- ============================================
-- 3. COMPLEX SQL QUERIES (For Reports)
-- ============================================

-- Query 1: Total animals per species (JOIN + GROUP BY + COUNT)
SELECT s.common_name, COUNT(a.animal_id) AS total_animals
FROM species s
LEFT JOIN animals a ON s.species_id = a.species_id
GROUP BY s.common_name
ORDER BY total_animals DESC;

-- Query 2: Threatened species (WHERE IN)
SELECT common_name, conservation_status
FROM species
WHERE conservation_status IN ('Endangered', 'Critically Endangered', 'Vulnerable')
ORDER BY conservation_status;

-- Query 3: Observations per protected area (LEFT JOIN + GROUP BY)
SELECT p.area_name, COUNT(o.observation_id) AS total_observations
FROM protected_areas p
LEFT JOIN observations o ON p.area_id = o.area_id
GROUP BY p.area_name
ORDER BY total_observations DESC;

-- Query 4: Recent observations with 5-table JOIN
SELECT 
    o.observation_id,
    s.common_name AS species,
    u.full_name AS ranger_name,
    p.area_name,
    o.observation_date,
    o.health_condition
FROM observations o
JOIN animals a ON o.animal_id = a.animal_id
JOIN species s ON a.species_id = s.species_id
JOIN rangers r ON o.ranger_id = r.ranger_id
JOIN users u ON r.user_id = u.user_id
JOIN protected_areas p ON o.area_id = p.area_id
ORDER BY o.observation_date DESC;

-- Query 5: Average animals per species (AVG + Subquery)
SELECT AVG(animal_count) AS avg_animals_per_species
FROM (
    SELECT s.species_id, COUNT(a.animal_id) AS animal_count
    FROM species s
    LEFT JOIN animals a ON s.species_id = a.species_id
    GROUP BY s.species_id
) AS species_counts;

-- Query 6: Animals with health issues (WHERE NOT)
SELECT a.animal_id, s.common_name, a.health_status
FROM animals a
JOIN species s ON a.species_id = s.species_id
WHERE a.health_status != 'Healthy'
ORDER BY a.animal_id;

-- Query 7: Rangers with most observations (ORDER BY + LIMIT)
SELECT u.full_name, COUNT(o.observation_id) AS observation_count
FROM users u
JOIN rangers r ON u.user_id = r.user_id
JOIN observations o ON r.ranger_id = o.ranger_id
GROUP BY u.full_name
ORDER BY observation_count DESC
LIMIT 1;


-- ============================================
-- 4. PL/SQL FUNCTIONS
-- ============================================

-- Function 1: Count animals by species
CREATE OR REPLACE FUNCTION count_animals_by_species(p_species_id INTEGER)
RETURNS INTEGER AS $$
DECLARE
    total INTEGER;
BEGIN
    SELECT COUNT(*) INTO total
    FROM animals
    WHERE species_id = p_species_id;
    RETURN total;
END;
$$ LANGUAGE plpgsql;

-- Function 2: Add new species
CREATE OR REPLACE FUNCTION add_species(
    p_common_name VARCHAR,
    p_scientific_name VARCHAR,
    p_conservation_status VARCHAR
)
RETURNS INTEGER AS $$
DECLARE
    new_species_id INTEGER;
BEGIN
    INSERT INTO species (common_name, scientific_name, conservation_status)
    VALUES (p_common_name, p_scientific_name, p_conservation_status)
    RETURNING species_id INTO new_species_id;
    RETURN new_species_id;
END;
$$ LANGUAGE plpgsql;

-- Function 3: Add new animal
CREATE OR REPLACE FUNCTION add_animal(
    p_species_id INTEGER,
    p_gender VARCHAR,
    p_date_of_birth DATE,
    p_health_status VARCHAR
)
RETURNS INTEGER AS $$
DECLARE
    new_animal_id INTEGER;
BEGIN
    INSERT INTO animals (species_id, gender, date_of_birth, health_status)
    VALUES (p_species_id, p_gender, p_date_of_birth, p_health_status)
    RETURNING animal_id INTO new_animal_id;
    RETURN new_animal_id;
END;
$$ LANGUAGE plpgsql;

-- Function 4: Get species statistics (returns table)
CREATE OR REPLACE FUNCTION get_species_statistics()
RETURNS TABLE(
    species_name VARCHAR,
    total_animals BIGINT,
    conservation_status VARCHAR
) AS $$
BEGIN
    RETURN QUERY
    SELECT s.common_name, COUNT(a.animal_id)::BIGINT, s.conservation_status
    FROM species s
    LEFT JOIN animals a ON s.species_id = a.species_id
    GROUP BY s.species_id, s.common_name, s.conservation_status
    ORDER BY total_animals DESC;
END;
$$ LANGUAGE plpgsql;


-- ============================================
-- 5. TRIGGERS (Audit Trail)
-- ============================================

-- Trigger function for INSERT operations
CREATE OR REPLACE FUNCTION log_observation_insert()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO observation_log (observation_id, action_type, action_date)
    VALUES (NEW.observation_id, 'INSERT', NOW());
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger function for DELETE operations
CREATE OR REPLACE FUNCTION log_observation_delete()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO observation_log (observation_id, action_type, action_date)
    VALUES (OLD.observation_id, 'DELETE', NOW());
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

-- Create the triggers on observations table
CREATE TRIGGER after_observation_insert
    AFTER INSERT ON observations
    FOR EACH ROW
    EXECUTE FUNCTION log_observation_insert();

CREATE TRIGGER after_observation_delete
    AFTER DELETE ON observations
    FOR EACH ROW
    EXECUTE FUNCTION log_observation_delete();


-- ============================================
-- 6. VERIFICATION QUERIES
-- ============================================

-- Check all tables have data
SELECT 'roles' AS table_name, COUNT(*) AS record_count FROM roles
UNION ALL
SELECT 'users', COUNT(*) FROM users
UNION ALL
SELECT 'species', COUNT(*) FROM species
UNION ALL
SELECT 'animals', COUNT(*) FROM animals
UNION ALL
SELECT 'protected_areas', COUNT(*) FROM protected_areas
UNION ALL
SELECT 'rangers', COUNT(*) FROM rangers
UNION ALL
SELECT 'observations', COUNT(*) FROM observations
UNION ALL
SELECT 'observation_log', COUNT(*) FROM observation_log
ORDER BY table_name;

-- Show sample data from users table
SELECT user_id, username, full_name, role_id FROM users;

-- Show sample data from species table
SELECT species_id, common_name, scientific_name, conservation_status FROM species;

-- Show sample data from animals with species names
SELECT a.animal_id, s.common_name, a.gender, a.date_of_birth, a.health_status
FROM animals a
JOIN species s ON a.species_id = s.species_id
LIMIT 5;


-- ============================================
-- END OF DATABASE.SQL
-- ============================================