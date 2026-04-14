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
  - Access helpful information and support materials that allow user to save favourite resources and suggest new resources based on saved favourites.
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

Page	Preview :

Login Page	 <img width="1919" height="868" alt="Screenshot 2025-07-11 205218" src="https://github.com/user-attachments/assets/fd0d69f5-8c43-4133-bdb6-f25fcc2b8e39" />


Sign Up Page  
<img width="627" height="860" alt="Screenshot 2025-07-11 205318" src="https://github.com/user-attachments/assets/83661031-dab7-427e-b6b9-b50e386546d9" />


Dashboard 	<img width="1902" height="864" alt="Screenshot 2025-07-11 213802" src="https://github.com/user-attachments/assets/80f602d5-ceba-47d6-8283-73f222f0984a" />


Chat Interface	<img width="1919" height="869" alt="Screenshot 2025-07-11 213021" src="https://github.com/user-attachments/assets/4eec2e0d-0496-4348-b8cb-567ba17be528" />


Curated Resources Interface  <img width="1549" height="547" alt="Screenshot 2025-07-11 222345" src="https://github.com/user-attachments/assets/7a0450b5-0b11-4b53-ad79-ef02c400160e" />
<img width="1544" height="867" alt="Screenshot 2025-07-11 222404" src="https://github.com/user-attachments/assets/78887ea9-8c1e-41a1-b44a-1205bd998775" />


Self-Assesment Tools  <img width="1549" height="865" alt="Screenshot 2025-07-11 215357" src="https://github.com/user-attachments/assets/ee89512a-8e44-40ff-a73e-b6812b06b94b" />
<img width="1550" height="869" alt="Screenshot 2025-07-11 215415" src="https://github.com/user-attachments/assets/a4321793-5737-4c2e-9e71-396e28e2bda5" />
<img width="1550" height="800" alt="Screenshot 2025-07-11 215447" src="https://github.com/user-attachments/assets/63440379-e3a1-4a87-8cda-d88a82f0e0f8" />






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

Academic mentors, family members and peers who supported me throughout this project

⭐ If you find this project useful, consider giving it a star on GitHub!
