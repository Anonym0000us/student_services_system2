-- Enhanced Scholarship Module Database Upgrade
-- NEUST Gabaldon Campus Student Services Management System

-- Upgrade existing scholarships table
ALTER TABLE scholarships 
ADD COLUMN IF NOT EXISTS type VARCHAR(100) DEFAULT 'Academic' AFTER name,
ADD COLUMN IF NOT EXISTS amount DECIMAL(10,2) DEFAULT 0.00 AFTER eligibility,
ADD COLUMN IF NOT EXISTS requirements TEXT AFTER amount,
ADD COLUMN IF NOT EXISTS documents_required TEXT AFTER requirements,
ADD COLUMN IF NOT EXISTS max_applicants INT DEFAULT 0 AFTER documents_required,
ADD COLUMN IF NOT EXISTS current_applicants INT DEFAULT 0 AFTER max_applicants,
ADD COLUMN IF NOT EXISTS created_by INT AFTER current_applicants,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER created_by,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Create enhanced scholarship_applications table
CREATE TABLE IF NOT EXISTS scholarship_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT NOT NULL,
    user_id VARCHAR(50) NOT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'under_review', 'approved', 'rejected', 'withdrawn') DEFAULT 'pending',
    gpa DECIMAL(3,2),
    course VARCHAR(100),
    year_level VARCHAR(20),
    documents_submitted JSON,
    review_notes TEXT,
    rejection_reason TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_scholarship_id (scholarship_id)
);

-- Create scholarship_documents table for file management
CREATE TABLE IF NOT EXISTS scholarship_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES scholarship_applications(id) ON DELETE CASCADE,
    INDEX idx_application_id (application_id),
    INDEX idx_document_type (document_type)
);

-- Create scholarship_notifications table
CREATE TABLE IF NOT EXISTS scholarship_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
);

-- Create scholarship_audit_log table
CREATE TABLE IF NOT EXISTS scholarship_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    old_values JSON,
    new_values JSON,
    user_id VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_record_id (record_id),
    INDEX idx_user_id (user_id)
);

-- Create scholarship_reports table for analytics
CREATE TABLE IF NOT EXISTS scholarship_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(100) NOT NULL,
    report_data JSON NOT NULL,
    generated_by VARCHAR(50) NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_type (report_type),
    INDEX idx_generated_by (generated_by)
);

-- Insert sample scholarship types
INSERT IGNORE INTO scholarships (name, type, description, eligibility, amount, requirements, documents_required, max_applicants, status) VALUES
('NEUST Academic Excellence Scholarship', 'Academic', 'Full tuition fee scholarship for students with outstanding academic performance', 'GPA 1.75 or better, No failing grades, Full-time student', 50000.00, 'Must maintain GPA 1.75, Submit quarterly progress report, Participate in community service', '["Transcript of Records", "Certificate of Enrollment", "GWA Certificate", "Recommendation Letter"]', 50, 'active'),
('NEUST Leadership Scholarship', 'Leadership', 'Partial scholarship for student leaders and organization officers', 'Active in student organizations, Good academic standing, Leadership experience', 25000.00, 'Maintain leadership position, Good academic standing, Submit leadership report', '["Transcript of Records", "Certificate of Leadership", "Organization Endorsement", "Essay"]', 30, 'active'),
('NEUST Financial Aid Grant', 'Need-based', 'Financial assistance for students with demonstrated financial need', 'Family income below poverty line, Good academic standing, No other scholarships', 15000.00, 'Maintain good academic standing, Submit financial documents annually', '["Transcript of Records", "Income Certificate", "Barangay Certificate", "Tax Declaration"]', 100, 'active');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_scholarship_status ON scholarships(status);
CREATE INDEX IF NOT EXISTS idx_scholarship_deadline ON scholarships(deadline);
CREATE INDEX IF NOT EXISTS idx_scholarship_type ON scholarships(type);
CREATE INDEX IF NOT EXISTS idx_application_status ON scholarship_applications(status);
CREATE INDEX IF NOT EXISTS idx_application_date ON scholarship_applications(application_date);