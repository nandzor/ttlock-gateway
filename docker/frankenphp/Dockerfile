# ==============================================================================
# Dockerfile Single-Stage Build untuk Aplikasi Laravel dengan FrankenPHP
#
# Strategi ini menggabungkan semua langkah (instalasi dependensi,
# build, dan runtime) ke dalam satu image. Image final akan lebih besar
# daripada multi-stage build tetapi lebih sederhana.
#
# PENTING: Untuk build yang optimal dan aman, buat file .dockerignore
# di root proyek Anda untuk mengecualikan file seperti .git, .env,
# node_modules, dan file lokal lainnya agar tidak disalin ke dalam image.
# ==============================================================================

FROM dunglas/frankenphp:1-php8.3

WORKDIR /app

# Install semua dependensi sistem yang dibutuhkan.
# Paket "-dev" akan secara otomatis menginstall library runtime yang diperlukan.
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev \
    libmagickwand-dev \
    libsodium-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_pgsql zip gd mbstring xml bcmath opcache intl exif pcntl sockets sodium \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Caddyfile terlebih dahulu
COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Copy seluruh kode aplikasi
COPY . .

# Install Composer dependencies untuk production
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Create necessary directories and set permissions
RUN mkdir -p /var/log/caddy \
    && chmod 755 /app \
    && chmod 755 /var/log/caddy

# Konfigurasi FrankenPHP untuk web + queue processing
ENV FRANKENPHP_CONFIG="worker:16 scheduler:2"
ENV QUEUE_CONNECTION=redis
ENV REDIS_HOST=redis
ENV REDIS_PORT=6379

# Expose port 80
EXPOSE 80 443

# Start FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

