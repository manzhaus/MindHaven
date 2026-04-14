# MindHaven
**A Digital Sanctuary for Mental Wellness**

MindHaven is a web-based mental wellness platform designed to promote emotional well-being through intuitive tools and a user-friendly interface. It provides a safe and supportive environment where users can track moods, access mental health resources, and manage their personal wellness journey.

---

## 📌 Overview
In today’s fast-paced world, mental health is often overlooked. MindHaven aims to bridge this gap by offering a centralized platform that empowers individuals to monitor and improve their mental well-being.

This project was developed as part of an academic and portfolio initiative to demonstrate full-stack web development skills using modern technologies.

---

## ✨ Features
- 🔐 **User Authentication**
  - Secure login and registration system.
- 📊 **Real Time Communication**
  - Communicate with counselor in real time via chat box.
- 📝 **Self-Assessment Tools**
  - Record your mental health score using certified self-assessment tools like PHQ-9 and GAD-7 with results history.
- 📚 **Mental Health Resources**
  - Access helpful information and support materials that allow user to save favourite resources.
- 📱 **Responsive Design**
  - Optimized for desktops, tablets, and mobile devices.
- 🗄️ **Database Integration**
  - Stores and retrieves user data efficiently using MySQL.

---

## 🛠️ Technologies Used

| Category           | Technology            |
|--------------------|-----------------------|
| Frontend           | HTML, CSS, JavaScript |
| Backend            | PHP                   |
| Database           | MySQL                 |
| Local Server       | XAMPP                 |
| Real-Time API      | Ably API              |
| Repository Hosting | GitHub                |

---

## ⚙️ Installation and Setup Guide

Follow these steps to run MindHaven locally.

### 1️⃣ Clone the Repository

git clone https://github.com/yourusername/MindHaven.git
cd MindHaven

2️⃣ Set Up the Local Server
Install XAMPP.
Start Apache and MySQL from the XAMPP Control Panel.

3️⃣ Move the Project

Copy the project folder into:

C:\xampp\htdocs\

4️⃣ Create the Database

Open your browser and go to:

http://localhost/phpmyadmin

Create a new database named:

mindhaven

Import the SQL file located in:

database/mindhaven.sql

5️⃣ Configure the Database Connection

Open the configuration file and update the credentials if necessary.

<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "mindhaven";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

6️⃣ Run the Application

Open your browser and navigate to:

http://localhost/MindHaven

---

📸 Screenshots

Add screenshots of your application here to showcase its interface.

Page	Preview

Login Page	<img width="1898" height="868" alt="Screenshot 2025-07-11 204826" src="https://github.com/user-attachments/assets/da80dd5e-8b6d-495b-8735-a1ada857984f" />

Sign Up Page  <img width="627" height="860" alt="Screenshot 2025-07-11 205318" src="https://github.com/user-attachments/assets/59d930bf-be32-44fa-b11e-592bdb577bea" />

Dashboard 	<img width="1902" height="864" alt="Screenshot 2025-07-11 213802" src="https://github.com/user-attachments/assets/07e32554-2062-4741-808f-c11ab43c8327" />

Chat Interface	<img width="1919" height="869" alt="Screenshot 2025-07-11 213021" src="https://github.com/user-attachments/assets/5a4617a0-bf87-4805-b76a-73cdaa1f452c" />

Curated Resources Interface  <img width="1549" height="547" alt="Screenshot 2025-07-11 222345" src="https://github.com/user-attachments/assets/412559b6-f01d-4e27-abe3-1ceb12c7741d" />
<img width="1544" height="867" alt="Screenshot 2025-07-11 222404" src="https://github.com/user-attachments/assets/c0daac21-111c-49fd-9374-0a25557a934e" />

Self-Assesment Tools  <img width="1549" height="865" alt="Screenshot 2025-07-11 215357" src="https://github.com/user-attachments/assets/6f42bd17-e781-4025-8bbb-d1a09d84cf73" />
<img width="1550" height="800" alt="Screenshot 2025-07-11 215447" src="https://github.com/user-attachments/assets/cc0eca86-243a-4661-bd71-5975f5625cd4" />



🚀 Future Enhancements:


Deploy to a live hosting platform

Advanced analytics and mood visualization

Reminder and notification system

AI-powered mental health chatbot

Cloud database integration

👨‍💻 Author:
Muhammad Fakhrul Iman Bin Ahmad Amini

Fresh Graduate in Software Engineering / Computer Science

GitHub: https://github.com/manzhaus

LinkedIn: https://www.linkedin.com/in/muhammad-fakhrul-iman/

This project is developed for educational and portfolio purposes. You are free to use and modify it with proper attribution.

💙 Acknowledgements
Open-source development community
Mental health awareness initiatives
Academic mentors and peers who supported this project

⭐ If you find this project useful, consider giving it a star on GitHub!
