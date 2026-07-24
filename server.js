const express = require('express');
const path = require('path');
const cookieParser = require('cookie-parser');
const helmet = require('helmet');
const csrf = require('csurf');
const rateLimit = require('express-rate-limit');
const dotenv = require('dotenv');
const session = require('express-session');
const { db } = require('./src/db');
const routes = require('./src/routes');

dotenv.config();

const app = express();
const port = process.env.PORT || 3000;

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(helmet());
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: false }));
app.use(express.json());
app.use(cookieParser());
app.use(session({
  secret: process.env.SESSION_SECRET || 'meusegredo-secret',
  resave: false,
  saveUninitialized: true,
  cookie: { secure: process.env.NODE_ENV === 'production', httpOnly: true, maxAge: 1000 * 60 * 60 * 24 }
}));

const csrfProtection = csrf({ cookie: true });

const limiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 200,
  standardHeaders: true,
  legacyHeaders: false,
});
app.use(limiter);

app.use((req, res, next) => {
  res.locals.env = process.env.NODE_ENV || 'development';
  res.locals.currentUrl = req.originalUrl;
  res.locals.notificationsEnabled = req.session.notificationsEnabled || false;
  res.locals.success = req.session.success || null;
  delete req.session.success;
  next();
});

app.use('/', routes({ csrfProtection }));

app.use((err, req, res, next) => {
  if (err.code === 'EBADCSRFTOKEN') {
    return res.status(403).send('Formulário inválido. Atualize a página e tente novamente.');
  }
  console.error(err);
  res.status(500).render('error', { message: 'Ocorreu um erro interno. Tente novamente mais tarde.' });
});

app.listen(port, () => {
  console.log(`meusegredo.shop rodando em http://localhost:${port}`);
});
