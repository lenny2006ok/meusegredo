FROM php:8.3-apache

# Instalar extensões PHP necessárias (PDO MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar o módulo mod_rewrite do Apache
RUN a2enmod rewrite

# Definir o diretório de trabalho do Apache
WORKDIR /var/www/html

# Copiar os arquivos da aplicação para o contêiner
COPY . .

# Ajustar as permissões para o servidor Apache (www-data) conseguir escrever nos diretórios necessários (como logs)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/logs

# Configurar a porta do Apache
EXPOSE 80

# Iniciar o servidor Apache em primeiro plano
CMD ["apache2-foreground"]
