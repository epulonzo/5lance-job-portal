-- 5Lance Job Portal — sample seed data
-- Run AFTER schema.sql. Password for every seeded user is: Password123
-- (hash generated with PHP password_hash('Password123', PASSWORD_BCRYPT))

USE lance5_db;

INSERT INTO users (name, email, password_hash, role, bio, skills) VALUES
('Aiman Admin', 'admin@5lance.com', '$2y$10$xd5EbMKpfzq6tByhp4kZXemNsy2pt6NYGN2.aT7BnfTTzPeigLHxG', 'admin', 'Platform administrator', NULL),
('Amyzal Zikrullah', 'amyzal@5lance.com', '$2y$10$xd5EbMKpfzq6tByhp4kZXemNsy2pt6NYGN2.aT7BnfTTzPeigLHxG', 'client', 'Startup founder looking for talented freelancers.', NULL),
('Adam Iskandar', 'adam@5lance.com', '$2y$10$xd5EbMKpfzq6tByhp4kZXemNsy2pt6NYGN2.aT7BnfTTzPeigLHxG', 'freelancer', 'Frontend developer specialising in Vue.js and Tailwind CSS.', 'Vue.js, JavaScript, Tailwind CSS, HTML, CSS'),
('Saiful Aqil', 'saiful@5lance.com', '$2y$10$xd5EbMKpfzq6tByhp4kZXemNsy2pt6NYGN2.aT7BnfTTzPeigLHxG', 'freelancer', 'UI/UX designer and frontend engineer.', 'Figma, UI Design, Vue.js, Responsive Design');

INSERT INTO jobs (client_id, title, description, category, budget, deadline, status) VALUES
(2, 'Build a responsive landing page', 'We need a modern, responsive landing page built with Vue 3 and Tailwind CSS for our startup launch.', 'Development', 1200.00, '2026-07-15', 'open'),
(2, 'Redesign mobile app UI', 'Looking for a UI/UX designer to redesign our mobile app screens in Figma with a clean, modern aesthetic.', 'Design', 900.00, '2026-07-01', 'open'),
(2, 'Write technical blog articles', 'Need 5 technical blog articles (1500 words each) about web development trends.', 'Writing', 500.00, '2026-06-30', 'open');

INSERT INTO applications (job_id, freelancer_id, cover_letter, proposed_rate, status) VALUES
(1, 3, 'I have 3 years of experience building landing pages with Vue and Tailwind. I would love to help bring your vision to life.', 1100.00, 'pending'),
(2, 4, 'I specialise in mobile UI redesigns and have a strong portfolio in Figma. Happy to share past work.', 850.00, 'pending');
