const express = require('express');
const path = require('path');
const db = require('./db');

const app = express();
const PORT = process.env.PORT || 3000;

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Initialize SQLite Database Tables
async function initDb() {
  try {
    await db.query(`
      CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NULL,
        debt_balance REAL DEFAULT 0.00,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      );
    `);

    await db.query(`
      CREATE TABLE IF NOT EXISTS jobs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        job_title TEXT NOT NULL,
        customer_id INTEGER NOT NULL,
        quantity INTEGER DEFAULT 1,
        unit_price REAL DEFAULT 0.00,
        total_price REAL DEFAULT 0.00,
        amount_paid REAL DEFAULT 0.00,
        status TEXT DEFAULT 'pending',
        notes TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
      );
    `);

    const existingWalkIn = await db.query("SELECT * FROM customers WHERE id = 1");
    if (existingWalkIn.length === 0) {
      await db.query(`
        INSERT INTO customers (id, name, phone, email) 
        VALUES (1, 'Walk-in Customer', '0246116269', 'info@handsontech.com')
      `);
    }

    console.log("SQLite database initialized successfully.");
  } catch (err) {
    console.error("Error initializing SQLite database:", err);
  }
}

initDb();

// Main Dashboard
app.get('/', async (req, res) => {
  try {
    const pendingRes = await db.query("SELECT COUNT(*) AS pending_count FROM jobs WHERE status='pending'");
    const progressRes = await db.query("SELECT COUNT(*) AS progress_count FROM jobs WHERE status='in_progress'");
    const completedRes = await db.query("SELECT COUNT(*) AS completed_count FROM jobs WHERE status='completed'");
    const revenueRes = await db.query("SELECT COALESCE(SUM(amount_paid), 0) AS total_revenue FROM jobs");

    const pending_count = pendingRes[0].pending_count;
    const progress_count = progressRes[0].progress_count;
    const completed_count = completedRes[0].completed_count;
    const total_revenue = revenueRes[0].total_revenue;

    const jobs = await db.query(`
      SELECT j.*, c.name as customer_name, c.phone 
      FROM jobs j 
      JOIN customers c ON j.customer_id = c.id 
      ORDER BY j.id DESC
    `);

    const customers = await db.query("SELECT * FROM customers ORDER BY name ASC");

    res.render('index', {
      metrics: { pending_count, progress_count, completed_count, total_revenue },
      jobs,
      customers
    });
  } catch (err) {
    console.error("Database Error:", err);
    res.status(500).send("Database Error: " + err.message);
  }
});

// Add Customer
app.post('/add-customer', async (req, res) => {
  try {
    const { name, phone, email } = req.body;
    await db.query("INSERT INTO customers (name, phone, email) VALUES (?, ?, ?)", [
      name.trim(),
      phone.trim(),
      email ? email.trim() : null
    ]);
    res.redirect('/');
  } catch (err) {
    console.error(err);
    res.status(500).send("Error adding customer: " + err.message);
  }
});

// Create Job
app.post('/create-job', async (req, res) => {
  try {
    const { job_title, customer_id, quantity, unit_price, amount_paid, status } = req.body;
    const qty = parseInt(quantity) || 1;
    const uPrice = parseFloat(unit_price) || 0;
    const paid = parseFloat(amount_paid) || 0;
    const totalPrice = qty * uPrice;
    const jobStatus = status || 'pending';

    await db.query(
      "INSERT INTO jobs (job_title, customer_id, quantity, unit_price, total_price, amount_paid, status) VALUES (?, ?, ?, ?, ?, ?, ?)",
      [job_title.trim(), customer_id, qty, uPrice, totalPrice, paid, jobStatus]
    );

    if (paid < totalPrice) {
      const debt = totalPrice - paid;
      await db.query("UPDATE customers SET debt_balance = debt_balance + ? WHERE id = ?", [debt, customer_id]);
    }

    res.redirect('/');
  } catch (err) {
    console.error(err);
    res.status(500).send("Error creating job: " + err.message);
  }
});

// Update Job Status
app.post('/update-job-status', async (req, res) => {
  try {
    const { job_id, status } = req.body;
    await db.query("UPDATE jobs SET status = ? WHERE id = ?", [status, job_id]);
    res.redirect('/');
  } catch (err) {
    console.error(err);
    res.status(500).send("Error updating job status: " + err.message);
  }
});

// Generate Printable Invoice
app.get('/invoice/:id', async (req, res) => {
  try {
    const rows = await db.query(`
      SELECT j.*, c.name as customer_name, c.phone, c.email 
      FROM jobs j 
      JOIN customers c ON j.customer_id = c.id 
      WHERE j.id = ?
    `, [req.params.id]);

    if (rows.length === 0) return res.status(404).send("Invoice not found.");
    res.render('invoice', { job: rows[0] });
  } catch (err) {
    console.error(err);
    res.status(500).send("Error loading invoice: " + err.message);
  }
});

app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
