# Parliamentary Feedback Platform

This repository contains the full source code for the **Parliamentary Feedback Platform**, a PHP-based web application designed to streamline communication between citizens and parliament members in Lesotho. It allows citizens to submit petitions, feedback, and suggestions, while enabling parliamentarians to manage and respond efficiently.

---

## 🌟 Features

- Citizen registration and login  
- MP (Member of Parliament) dashboard for petitions, feedback, and reports  
- Submission of petitions, feedback, and suggestions online  
- Admin panel to manage MPs, citizens, and submissions  
- Order papers with agenda items and attachments  
- PDF generation for petitions and reports  
- Responsive design using HTML, PHP, CSS, and Bootstrap  
- MySQL database for secure data storage  

---

## 📂 Project Structure
mendla/
├── admin/ # Admin dashboard
├── citizen/ # Citizen pages
├── member_of_parlament/ # MP dashboard
├── includes/ # Database connection, functions
├── css/ # Stylesheets
├── images/ # Image assets
├── fpdf186/ # FPDF library for PDF generation
├── about.html
├── footer.html
├── header.html
├── index.php
├── login.html
├── logout.php
├── register.html
├── suggestions.php
├── credentials.txt # Do not commit secrets
└── .gitignore



---

## 🚀 Installation

1. **Clone the repository**  

   ```bash
   git clone https://github.com/wesimosiuoa/Parliamentary-Feedback.git
   cd Parliamentary-Feedback
C:\xampp\htdocs\Parliamentary-Feedback
C:\xampp\htdocs\Parliamentary-Feedback
Import the database

Create a new MySQL database (e.g. parliamentary_feedback).

Import the provided SQL file if included in the repository (or create tables manually).

Configure database credentials

Edit includes/dbcon.inc.php to set your MySQL host, username, password, and database name.

Run the application
http://localhost/Parliamentary-Feedback


## 📝 Usage

Citizens can register and submit petitions or feedback.

MPs can log in via the MP dashboard to view, manage, and respond to citizen input.

Admin users can oversee all data and generate PDF reports.
