# meusegredo.shop

Plataforma anônima para compartilhar segredos, desabafos e apoio emocional.

## Estrutura do projeto

- `server.js` - servidor Express principal.
- `src/db.js` - conexão com MySQL/MariaDB.
- `src/routes.js` - rotas principais e API.
- `src/utils.js` - utilitários e pseudônimos.
- `views/` - templates EJS.
- `public/css/styles.css` - estilos.
- `public/js/app.js` - lógica frontend.
- `.env.example` - variáveis de ambiente.
- `src/backup.js` - script de backup do banco.

## Instalação

1. Instale Node.js 18+.
2. Clone ou copie o projeto.
3. Rode `npm install`.
4. Copie `.env.example` para `.env` e configure:
   - `DB_HOST`
   - `DB_PORT`
   - `DB_USER`
   - `DB_PASSWORD`
   - `DB_DATABASE`
   - `ADMIN_USER`
   - `ADMIN_PASS`
   - `SESSION_SECRET`
5. Crie o banco com as queries SQL abaixo.
6. Inicie com `npm start`.

## Comandos

- `npm start` - iniciar servidor.
- `npm run dev` - iniciar com `nodemon`.
- `npm run backup` - criar backup SQL diário.

## Banco de dados

Use o arquivo `database.sql` para criar as tabelas.

## Deploy

Sugestões:
- hospedagem compartilhada com suporte Node.js e MySQL/MariaDB;
- VPS Linux com Nginx, PM2 e MySQL/MariaDB;
- DigitalOcean, Linode, Render ou Vercel + ClearDB.

## Observações

- Tema escuro padrão.
- Suporta feed infinito e filtros básicos.
- CSRF, sanitização e prepared statements.
- Aviso legal incluído no rodapé.
