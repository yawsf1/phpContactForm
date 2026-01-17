# SecureOnePagePHP

A **single-page PHP project** demonstrating a secure contact form with:

- Input sanitization & validation  
- CSRF protection  
- SQL injection prevention (prepared statements)  
- Password/email validation  

Everything runs on **one page** with inline CSS for a modern, responsive design.  

## Setup

1. Create a MySQL database, e.g., `secure_contact`.  
2. Import the `database.sql` schema:

```sql
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
