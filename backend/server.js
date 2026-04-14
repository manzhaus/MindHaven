// server.js

const express = require('express');
const mysql = require('mysql');
const bodyParser = require('body-parser');
const cors = require('cors');

// Create an Express application
const app = express();

// Middleware setup
app.use(bodyParser.json());
app.use(cors());

// Database connection
const db = mysql.createConnection({
  host: 'localhost', // Database host
  user: 'root',      // Database username (default MySQL user)
  password: '',      // No password
  database: 'mindhaven' // Database name
});

// Connect to the database
db.connect((err) => {
  if (err) {
    console.error('Error connecting to the database:', err.stack);
    return;
  }
  console.log('Connected to the MySQL database as id ' + db.threadId);
});

// Route to test the connection
app.get('/', (req, res) => {
  res.send('MindHaven API is running');
});

// User registration route
app.post('/register', (req, res) => {
  const { username, email, password } = req.body;

  // Validate input
  if (!username || !email || !password) {
    return res.status(400).json({ error: 'Please provide all fields' });
  }

  // Insert user into the database
  const query = 'INSERT INTO users (username, email, password) VALUES (?, ?, ?)';
  db.query(query, [username, email, password], (err, result) => {
    if (err) {
      console.error('Error inserting user:', err);
      return res.status(500).json({ error: 'Failed to register user' });
    }
    res.status(200).json({ message: 'User registered successfully', userId: result.insertId });
  });
});

// User login route
app.post('/login', (req, res) => {
  const { email, password } = req.body;

  // Validate input
  if (!email || !password) {
    return res.status(400).json({ error: 'Please provide both email and password' });
  }

  // Check if user exists
  const query = 'SELECT * FROM users WHERE email = ? AND password = ?';
  db.query(query, [email, password], (err, result) => {
    if (err) {
      console.error('Error checking login credentials:', err);
      return res.status(500).json({ error: 'Failed to log in' });
    }
    if (result.length > 0) {
      // User exists, login successful
      res.status(200).json({ message: 'Login successful', user: result[0] });
    } else {
      // User not found or incorrect password
      res.status(401).json({ error: 'Invalid email or password' });
    }
  });
});

// Start the server
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
