FROM php:8.2-apache

# Instala extensiones necesarias (mysqli para tu conexión a base de datos)
RUN docker-php-ext-install mysqli

# Habilita mod_rewrite por si usas URLs amigables
RUN a2enmod rewrite

# Habilita mod_headers, requerido para las cabeceras de seguridad del .htaccess
RUN a2enmod headers

# Copia todo el proyecto al directorio raíz de Apache
COPY . /var/www/html/

# Instala Composer y las dependencias del proyecto
WORKDIR /var/www/html

# Redirige la raíz del sitio hacia Pages/Home.php
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    RedirectMatch ^/$ /Pages/Home.php\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf
# Garantiza que logs/ exista y sea escribible, independientemente de si
# el volumen montado en runtime la trae vacia o la oculta
RUN mkdir -p /var/www/html/logs && chmod 775 /var/www/html/logs

# Ajusta permisos (por si tu app escribe archivos, logs, etc.)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80