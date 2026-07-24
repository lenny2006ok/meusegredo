const express = require('express');
const sanitizeHtml = require('sanitize-html');
const { db } = require('./db');
const { randomPseudo, categories, offensiveFilter } = require('./utils');

const PSEUDONYMS = [
  'Lobo Cinza', 'Lua Azul', 'Raposa Vermelha', 'Coruja', 'Águia Solitária',
  'Pomba Branca', 'Gato Preto', 'Fênix', 'Dragão', 'Cavalo Marinho',
  'Andarilho', 'Sombra', 'Nuvem Passageira', 'Folha ao Vento', 'Estrela Cadente'
];

function routes({ csrfProtection }) {
  const router = express.Router();

  router.get('/', async (req, res) => {
    const filter = req.query.filter || 'recent';
    const category = req.query.category || null;
    const page = parseInt(req.query.page, 10) || 1;
    const pageSize = 10;
    const offset = (page - 1) * pageSize;
    const filterSql = {
      recent: 'ORDER BY p.created_at DESC',
      likes: 'ORDER BY p.likes DESC',
      comments: 'ORDER BY comment_count DESC',
      random: 'ORDER BY RAND()',
      trending: 'ORDER BY (p.likes + comment_count) DESC'
    }[filter] || 'ORDER BY p.created_at DESC';
    const whereClause = category ? 'WHERE p.category = ?' : '';
    const queryParams = category ? [category, pageSize, offset] : [pageSize, offset];

    const [posts] = await db.query(`
      SELECT p.*, IFNULL(c.comment_count, 0) AS comment_count
      FROM posts p
      LEFT JOIN (
        SELECT post_id, COUNT(*) AS comment_count FROM comments GROUP BY post_id
      ) c ON c.post_id = p.id
      ${whereClause}
      ${filterSql}
      LIMIT ? OFFSET ?
    `, queryParams);

    res.render('index', {
      posts,
      filter,
      categories,
      page,
      totalPages: 0,
      pseudonyms: PSEUDONYMS,
      selectedCategory: category
    });
  });

  router.get('/api/posts', async (req, res) => {
    const filter = req.query.filter || 'recent';
    const category = req.query.category || null;
    const page = parseInt(req.query.page, 10) || 1;
    const pageSize = 10;
    const offset = (page - 1) * pageSize;
    const filterSql = {
      recent: 'ORDER BY p.created_at DESC',
      likes: 'ORDER BY p.likes DESC',
      comments: 'ORDER BY comment_count DESC',
      random: 'ORDER BY RAND()',
      trending: 'ORDER BY (p.likes + comment_count) DESC'
    }[filter] || 'ORDER BY p.created_at DESC';
    const whereClause = category ? 'WHERE p.category = ?' : '';
    const queryParams = category ? [category, pageSize, offset] : [pageSize, offset];

    const [posts] = await db.query(`
      SELECT p.*, IFNULL(c.comment_count, 0) AS comment_count
      FROM posts p
      LEFT JOIN (
        SELECT post_id, COUNT(*) AS comment_count FROM comments GROUP BY post_id
      ) c ON c.post_id = p.id
      ${whereClause}
      ${filterSql}
      LIMIT ? OFFSET ?
    `, queryParams);

    return res.json({ posts });
  });

  router.get('/contar', csrfProtection, (req, res) => {
    const a = Math.floor(Math.random() * 5) + 1;
    const b = Math.floor(Math.random() * 5) + 1;
    req.session.captchaAnswer = a + b;
    res.render('contar', { categories, csrfToken: req.csrfToken(), captchaQuestion: `${a} + ${b}` });
  });

  router.post('/contar', csrfProtection, async (req, res) => {
    const { title, category, secret, pseudonym, city, age, termsAccepted, honey, captchaAnswer } = req.body;
    if (honey) return res.redirect('/contar');
    if (!title || title.length > 100 || !secret || secret.length > 5000 || !termsAccepted) {
      return res.redirect('/contar');
    }
    if (parseInt(captchaAnswer, 10) !== req.session.captchaAnswer) {
      return res.redirect('/contar');
    }

    const safeTitle = sanitizeHtml(title, { allowedTags: [], allowedAttributes: {} });
    const safeSecret = sanitizeHtml(secret, { allowedTags: [], allowedAttributes: {} });
    const safePseudo = pseudonym ? sanitizeHtml(pseudonym, { allowedTags: [], allowedAttributes: {} }) : randomPseudo(PSEUDONYMS);
    const safeCity = city ? sanitizeHtml(city, { allowedTags: [], allowedAttributes: {} }) : null;
    const safeAge = age ? parseInt(age, 10) : null;

    const banned = offensiveFilter(safeTitle) || offensiveFilter(safeSecret);
    if (banned) {
      return res.redirect('/contar');
    }

    const ip = req.ip;
    const [bannedIp] = await db.query('SELECT id FROM banned_ips WHERE ip_address = ? LIMIT 1', [ip]);
    if (bannedIp.length) {
      return res.redirect('/contar');
    }

    await db.query(`
      INSERT INTO posts (title, category, secret_text, pseudonym, city, age, likes, dislikes, views, created_at)
      VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, NOW())
    `, [safeTitle, category, safeSecret, safePseudo, safeCity, safeAge]);

    req.session.success = 'Seu segredo foi publicado com sucesso!';
    return res.redirect('/');
  });

  router.get('/segredo/:id', async (req, res) => {
    const id = parseInt(req.params.id, 10);
    const [rows] = await db.query('SELECT * FROM posts WHERE id = ?', [id]);
    if (!rows.length) return res.status(404).render('not-found');
    const post = rows[0];

    await db.query('UPDATE posts SET views = views + 1 WHERE id = ?', [id]);
    const [comments] = await db.query('SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC', [id]);
    const nestedComments = buildCommentTree(comments);

    res.render('segredo', { post, comments: nestedComments, categories });
  });

  router.get('/admin/login', csrfProtection, (req, res) => {
    res.render('admin-login', { csrfToken: req.csrfToken(), error: null });
  });

  router.post('/admin/login', csrfProtection, async (req, res) => {
    const { username, password } = req.body;
    const adminUser = process.env.ADMIN_USER || 'admin';
    const adminPass = process.env.ADMIN_PASS || 'changeme';
    if (username === adminUser && password === adminPass) {
      req.session.adminUser = username;
      return res.redirect('/admin/dashboard');
    }
    return res.render('admin-login', { csrfToken: req.csrfToken(), error: 'Usuário ou senha incorretos.' });
  });

  router.post('/admin/logout', (req, res) => {
    req.session.adminUser = null;
    res.redirect('/admin/login');
  });

  function requireAdmin(req, res, next) {
    if (req.session.adminUser) return next();
    return res.redirect('/admin/login');
  }

  router.get('/admin/dashboard', requireAdmin, async (req, res) => {
    const [reports] = await db.query(`
      SELECT r.*, p.title AS post_title, c.text AS comment_text
      FROM reports r
      LEFT JOIN posts p ON p.id = r.post_id
      LEFT JOIN comments c ON c.id = r.comment_id
      ORDER BY r.created_at DESC
      LIMIT 50
    `);
    const [recentPosts] = await db.query('SELECT * FROM posts ORDER BY created_at DESC LIMIT 20');
    res.render('admin-dashboard', { reports, recentPosts });
  });

  router.post('/admin/delete-post', requireAdmin, async (req, res) => {
    const { postId } = req.body;
    if (!postId) return res.redirect('/admin/dashboard');
    await db.query('DELETE FROM comments WHERE post_id = ?', [postId]);
    await db.query('DELETE FROM posts WHERE id = ?', [postId]);
    res.redirect('/admin/dashboard');
  });

  router.post('/admin/delete-comment', requireAdmin, async (req, res) => {
    const { commentId } = req.body;
    if (!commentId) return res.redirect('/admin/dashboard');
    await db.query('DELETE FROM comments WHERE id = ? OR parent_id = ?', [commentId, commentId]);
    res.redirect('/admin/dashboard');
  });

  router.post('/api/like', async (req, res) => {
    const { postId, action } = req.body;
    const allowed = ['like', 'dislike'];
    if (!allowed.includes(action) || !postId) {
      return res.status(400).json({ success: false });
    }

    const cookieKey = action === 'like' ? `liked_${postId}` : `disliked_${postId}`;
    if (req.cookies[cookieKey]) {
      return res.json({ success: false, message: 'Você já interagiu com este segredo.' });
    }

    await db.query(`UPDATE posts SET ${action === 'like' ? 'likes' : 'dislikes'} = ${action === 'like' ? 'likes' : 'dislikes'} + 1 WHERE id = ?`, [postId]);
    res.cookie(cookieKey, '1', { maxAge: 1000 * 60 * 60 * 24 * 30, httpOnly: true });
    res.json({ success: true });
  });

  router.post('/api/comment', csrfProtection, async (req, res) => {
    const { postId, parentId, text } = req.body;
    if (!postId || !text || text.length > 2000) {
      return res.status(400).json({ success: false });
    }

    const safeText = sanitizeHtml(text, { allowedTags: [], allowedAttributes: {} });
    const pseudo = randomPseudo(PSEUDONYMS);

    await db.query(
      'INSERT INTO comments (post_id, parent_id, text, pseudonym, likes, created_at) VALUES (?, ?, ?, ?, 0, NOW())',
      [postId, parentId || null, safeText, pseudo]
    );

    res.json({ success: true, pseudonym: pseudo });
  });

  router.post('/api/report', csrfProtection, async (req, res) => {
    const { postId, commentId, reason } = req.body;
    const safeReason = sanitizeHtml(reason || '', { allowedTags: [], allowedAttributes: {} });
    await db.query('INSERT INTO reports (post_id, comment_id, reason, created_at) VALUES (?, ?, ?, NOW())', [postId || null, commentId || null, safeReason]);
    res.json({ success: true });
  });

  router.get('/ranking', async (req, res) => {
    const [today] = await db.query('SELECT * FROM posts ORDER BY views DESC LIMIT 10');
    const [week] = await db.query('SELECT p.*, COUNT(c.id) AS comment_count FROM posts p LEFT JOIN comments c ON c.post_id = p.id AND c.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY p.id ORDER BY comment_count DESC LIMIT 10');
    const [month] = await db.query('SELECT * FROM posts ORDER BY likes DESC LIMIT 10');
    res.render('ranking', { today, week, month });
  });

  router.get('/sobre', (req, res) => res.render('sobre'));
  router.get('/termos', (req, res) => res.render('termos'));

  function buildCommentTree(comments) {
    const map = {};
    comments.forEach(comment => { comment.children = []; map[comment.id] = comment; });
    const roots = [];
    comments.forEach(comment => {
      if (comment.parent_id) {
        if (map[comment.parent_id]) map[comment.parent_id].children.push(comment);
      } else {
        roots.push(comment);
      }
    });
    return roots;
  }

  return router;
}

module.exports = routes;
