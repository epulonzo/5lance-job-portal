-- 5Lance Job Portal — database schema
-- Matches Group6_Proposal.pdf §3.3 (users, jobs, applications)

CREATE DATABASE IF NOT EXISTS lance5_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lance5_db;

DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('freelancer', 'client', 'admin') NOT NULL,
    bio           TEXT NULL,
    skills        TEXT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE jobs (
    job_id      INT AUTO_INCREMENT PRIMARY KEY,
    client_id   INT NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category    VARCHAR(100) NOT NULL,
    budget      DECIMAL(10, 2) NULL,
    deadline    DATE NULL,
    status      ENUM('open', 'closed', 'awarded') NOT NULL DEFAULT 'open',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_jobs_client FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE applications (
    app_id         INT AUTO_INCREMENT PRIMARY KEY,
    job_id         INT NOT NULL,
    freelancer_id  INT NOT NULL,
    cover_letter   TEXT NOT NULL,
    proposed_rate  DECIMAL(10, 2) NULL,
    status         ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    applied_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_job FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_freelancer FOREIGN KEY (freelancer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT uq_application_per_job UNIQUE (job_id, freelancer_id)
);

CREATE INDEX idx_jobs_status ON jobs(status);
CREATE INDEX idx_jobs_category ON jobs(category);
CREATE INDEX idx_applications_job ON applications(job_id);
CREATE INDEX idx_applications_freelancer ON applications(freelancer_id);
