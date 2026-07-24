FROM node:18-alpine

# Definir diretório de trabalho
WORKDIR /app

# Configurar variáveis de ambiente para produção
ENV NODE_ENV=production

# Copiar arquivos de definição de dependências primeiro para cache do Docker
COPY package*.json ./

# Instalar dependências de produção apenas (limpando o cache do npm em seguida)
RUN npm ci --only=production && npm cache clean --force

# Copiar os arquivos e pastas da aplicação
COPY . .

# Expor a porta em que a aplicação Express roda
EXPOSE 3000

# Comando para iniciar a aplicação
CMD ["npm", "start"]
