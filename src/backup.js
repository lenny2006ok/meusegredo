const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const dotenv = require('dotenv');

dotenv.config();

const dumpFile = path.join(__dirname, '..', 'backups', `meusegredo_backup_${new Date().toISOString().slice(0,10)}.sql`);
const user = process.env.DB_USER || 'root';
const password = process.env.DB_PASSWORD || '';
const database = process.env.DB_DATABASE || 'meusegredo';

if (!fs.existsSync(path.dirname(dumpFile))) {
  fs.mkdirSync(path.dirname(dumpFile), { recursive: true });
}

const command = `mysqldump -u ${user} ${password ? `-p${password}` : ''} ${database} > "${dumpFile}"`;
exec(command, (error, stdout, stderr) => {
  if (error) {
    console.error('Erro no backup:', stderr || error.message);
    process.exit(1);
  }
  console.log('Backup criado em', dumpFile);
});
