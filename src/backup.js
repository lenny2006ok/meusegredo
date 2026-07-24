const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const dotenv = require('dotenv');

dotenv.config();

const dumpFile = path.join(__dirname, '..', 'backups', `banco_site_backup_${new Date().toISOString().slice(0,10)}.sql`);
const host = process.env.DB_HOST || 'banco-mariadb';
const port = process.env.DB_PORT || '3306';
const user = process.env.DB_USER || 'usuario_site';
const password = process.env.DB_PASSWORD || 'senha_db_segura';
const database = process.env.DB_DATABASE || 'banco_site';

if (!fs.existsSync(path.dirname(dumpFile))) {
  fs.mkdirSync(path.dirname(dumpFile), { recursive: true });
}

const command = `mysqldump -h ${host} -P ${port} -u ${user} ${password ? `-p"${password}"` : ''} ${database} > "${dumpFile}"`;
exec(command, (error, stdout, stderr) => {
  if (error) {
    console.error('Erro no backup:', stderr || error.message);
    process.exit(1);
  }
  console.log('Backup criado em', dumpFile);
});
