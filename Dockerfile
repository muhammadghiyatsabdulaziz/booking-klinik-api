FROM php:8.3-apache

# 1. Install dependensi sistem dan ekstensi PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# 2. Ambil Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Atur folder kerja
WORKDIR /var/www/html

# 4. Copy semua file proyek ke dalam container
COPY . .

# 5. Install pustaka Laravel (menggunakan --no-fund agar bersih dari notice funding)
RUN composer install --no-dev --optimize-autoloader --no-fund

# 6. Atur izin folder agar Laravel bisa menulis log dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Bersihkan cache Laravel bawaan lokal
RUN php artisan config:clear || true
RUN php artisan cache:clear || true

# 8. PERBAIKAN: Mengubah Document Root Apache langsung menggunakan path absolut (tanpa variabel petik tunggal)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/|/var/www/html/public/|g' /etc/apache2/apache2.conf

# 9. Buka port dan jalankan Apache
EXPOSE 80
CMD ["apache2-foreground"]
