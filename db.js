const sqlite3 = require('sqlite3').verbose();
const path = require('path');

const dbPath = path.join(__dirname, 'database.sqlite');
const db = new sqlite3.Database(dbPath, (err) => {
  if (err) {
    console.error('Error opening database:', err.message);
  } else {
    console.log('Connected to SQLite database.');
  }
});

// Auto-create database tables on startup
db.serialize(() => {
  db.run(`
    CREATE TABLE IF NOT EXISTS customers (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      phone TEXT NOT NULL,
      email TEXT,
      debt_balance REAL DEFAULT 0.00
    )
  `);

  db.run(`
    CREATE TABLE IF NOT EXISTS jobs (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      job_title TEXT NOT NULL,
      customer_id INTEGER NOT NULL,
      quantity INTEGER DEFAULT 1,
      unit_price REAL DEFAULT 0.00,
      total_price REAL DEFAULT 0.00,
      amount_paid REAL DEFAULT 0.00,
      status TEXT DEFAULT 'pending',
      FOREIGN KEY (customer_id) REFERENCES customers (id)
    )
  `);
});

// Helper function to return Promise array [rows] for async/await compatibility
db.query = function (sql, params = []) {
  return new Promise((resolve, reject) => {
    if (sql.trim().toUpperCase().startsWith('SELECT')) {
      db.all(sql, params, (err, rows) => {
        if (err) reject(err);
        else resolve([rows]);
      });
    } else {
      db.run(sql, params, function (err) {
        if (err) reject(err);
        else resolve([{ insertId: this.lastID, changes: this.changes }]);
      });
    }
  });
};

module.exports = db;
