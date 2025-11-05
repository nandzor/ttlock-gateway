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
    curl \
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

# Install Node.js dan npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
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

# Copy startup script
COPY docker/frankenphp/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Copy seluruh kode aplikasi
COPY . .

# Install Composer dependencies untuk production
RUN composer install --no-interaction --optimize-autoloader

# Install Node.js dependencies dan build assets Vite
RUN npm ci && npm run build && rm -rf node_modules

# Create storage directories and set permissions
RUN mkdir -p storage/app/private storage/framework/{cache,sessions,views} storage/logs \
    && chmod -R 775 storage bootstrap/cache

# Start FrankenPHP dan Horizon menggunakan startup script
CMD ["/usr/local/bin/start.sh"]

