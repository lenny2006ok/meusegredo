const mysql = require('mysql2/promise');
const dotenv = require('dotenv');

dotenv.config();

const pool = mysql.createPool({
  host: process.env.DB_HOST || 'banco-mariadb',
  port: process.env.DB_PORT || 3306,
  user: process.env.DB_USER || 'usuario_site',
  password: process.env.DB_PASSWORD || 'senha_db_segura',
  database: process.env.DB_DATABASE || 'banco_site',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  namedPlaceholders: true,
});

module.exports = {
  db: pool,
};
