-- Database schema for small business financial management system

-- Create the transactions table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    category ENUM('income', 'general_expense', 'travel', 'phone_data') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    vat_rate DECIMAL(5,2) DEFAULT 0, -- VAT rate in percent
    vat_amount DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better query performance
CREATE INDEX idx_date ON transactions(date);
CREATE INDEX idx_type ON transactions(type);
CREATE INDEX idx_category ON transactions(category);

-- Insert sample data for testing
INSERT INTO transactions (date, type, category, description, amount, vat_rate, vat_amount) VALUES
('2023-01-15', 'income', 'income', 'Sales revenue', 5000.00, 24.00, 1200.00),
('2023-01-20', 'expense', 'general_expense', 'Office supplies', 200.00, 24.00, 48.00),
('2023-01-25', 'expense', 'travel', 'Business trip to Helsinki', 150.00, 0.00, 0.00),
('2023-02-10', 'expense', 'phone_data', 'Monthly phone bill', 50.00, 24.00, 12.00),
('2023-03-05', 'income', 'income', 'Consulting services', 3000.00, 24.00, 720.00),
('2023-03-12', 'expense', 'general_expense', 'Software licenses', 150.00, 24.00, 36.00),
('2023-04-01', 'expense', 'travel', 'Client meeting expenses', 80.00, 0.00, 0.00),
('2023-04-15', 'expense', 'phone_data', 'Internet bill', 40.00, 24.00, 9.60);
