FROM php:8.2-apache

# Instala extensiones necesarias (mysqli para tu conexión a base de datos)
RUN docker-php-ext-install mysqli

# Habilita mod_rewrite por si usas URLs amigables
RUN a2enmod rewrite

# Copia todo el proyecto al directorio raíz de Apache
COPY . /var/www/html/

# Instala Composer y las dependencias del proyecto
WORKDIR /var/www/html

# Configura Home.php como archivo de entrada por defecto
RUN echo "DirectoryIndex Home.php index.php" > /etc/apache2/conf-available/directoryindex.conf \
    && a2enconf directoryindex

# Ajusta permisos (por si tu app escribe archivos, logs, etc.)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80