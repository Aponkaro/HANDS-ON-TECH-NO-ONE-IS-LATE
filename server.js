const express = require('express');
const path = require('path');
const db = require('./db');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Main Dashboard
app.get('/', async (req, res) => {
  try {
    const [pendingRes] = await db.query("SELECT COUNT(*) AS pending_count FROM jobs WHERE status='pending'");
    const [progressRes] = await db.query("SELECT COUNT(*) AS progress_count FROM jobs WHERE status='in_progress'");
    const [completedRes] = await db.query("SELECT COUNT(*) AS completed_count FROM jobs WHERE status='completed'");
    const [revenueRes] = await db.query("SELECT COALESCE(SUM(amount_paid), 0) AS total_revenue FROM jobs");

    const [jobs] = await db.query(`
      SELECT j.*, c.name as customer_name, c.phone 
      FROM jobs j 
      JOIN customers c ON j.customer_id = c.id 
      ORDER BY j.id DESC
    `);

    const [customers] = await db.query("SELECT * FROM customers ORDER BY name ASC");

    res.render('index', {
      metrics: { 
        pending_count: pendingRes[0]?.pending_count || 0, 
        progress_count: progressRes[0]?.progress_count || 0, 
        completed_count: completedRes[0]?.completed_count || 0, 
        total_revenue: revenueRes[0]?.total_revenue || 0 
      },
      jobs: jobs || [],
      customers: customers || []
    });
  } catch (err) {
    console.error("Database Error:", err);
    res.status(500).send("Database Connection Error: " + err.message);
  }
});

// Database Test Route
app.get('/test-db', async (req, res) => {
  try {
    const [rows] = await db.query('SELECT * FROM customers');
    res.json(rows);
  } catch (err) {
    console.error("Test DB Error:", err);
    res.status(500).send("Database Query Error: " + err.message);
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
    const [rows] = await db.query(`
      SELECT j.*, c.name as customer_name, c.phone, c.email 
      FROM jobs j 
      JOIN customers c ON j.customer_id = c.id 
      WHERE j.id = ?
    `, [req.params.id]);

    if (!rows || rows.length === 0) return res.status(404).send("Invoice not found.");
    res.render('invoice', { job: rows[0] });
  } catch (err) {
    console.error(err);
    res.status(500).send("Error loading invoice: " + err.message);
  }
});

app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
