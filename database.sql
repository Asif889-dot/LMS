-- 1. Database Creation
CREATE DATABASE IF NOT EXISTS lms_db;
USE lms_db;

-- 2. Users Table
-- Stores credentials and user roles
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Courses Table
-- Stores metadata for each course
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructor VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Enrollments Table
-- Handles the relationship between users and courses
-- Includes the 'status' column for professional soft-dropping logic
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'dropped') DEFAULT 'enrolled',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    -- Prevent a user from having two active rows for the same course
    UNIQUE KEY unique_user_course (user_id, course_id)
) ENGINE=InnoDB;

-- 5. Quiz Results Table (New)
-- Stores the outcomes of the course assessments
CREATE TABLE quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    score INT NOT NULL,
    passed TINYINT(1) DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Sample Data Injection
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@lms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('student1', 'student1@lms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

INSERT INTO courses (title, description, instructor) VALUES 
('Introduction to PHP', 'A comprehensive guide to backend development with PHP.', 'Dr. Smith'),
('Web Development with Bootstrap', 'Master responsive design and modern CSS frameworks.', 'Prof. Johnson'),
('Database Management', 'Deep dive into MySQL, architecture, and query optimization.', 'Dr. Lee'),
('Advanced JavaScript', 'Mastering ES6, Async/Await, and modern JS patterns.', 'Sarah Connor');
ALTER TABLE users ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active';
-- Run this in phpMyAdmin to ensure your table matches the code logic
ALTER TABLE users 
MODIFY COLUMN password VARCHAR(255) NOT NULL,
MODIFY COLUMN role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive') DEFAULT 'active';

-- Reset Super Admin (Password: admin123)
DELETE FROM users WHERE username = 'super_admin';
INSERT INTO users (username, email, password, role, status) 
VALUES ('super_admin', 'admin@lms-pro.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');
-- Create Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Enrollments Table (needed for the student count)
CREATE TABLE IF NOT EXISTS enrollments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) NOT NULL,
    student_id INT(11) NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
ALTER TABLE courses ADD COLUMN category VARCHAR(100) AFTER title;
ALTER TABLE courses ADD COLUMN IF NOT EXISTS category VARCHAR(100) AFTER title;
ALTER TABLE courses ADD COLUMN AFTER description IF NOT EXISTS FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE; 
-- Create Modules Table
CREATE TABLE IF NOT EXISTS modules (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    course_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    order_number INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Create Lessons Table
CREATE TABLE IF NOT EXISTS lessons (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    module_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    video_url VARCHAR(255),
    order_number INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS lesson_completions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);