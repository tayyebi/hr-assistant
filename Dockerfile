FROM php:8.3-cli

# Install required extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libgd-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Don't copy files - they will be mounted
# Install dependencies if composer.json exists
CMD ["sh", "-c", "if [ -f composer.json ]; then composer install --no-interaction; fi && php -S 0.0.0.0:8080 -t public"]
