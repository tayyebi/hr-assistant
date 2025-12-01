FROM php:8.2-cli

# Install required extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Don't copy files - they will be mounted
# Install dependencies if composer.json exists
CMD ["sh", "-c", "if [ -f composer.json ]; then composer install --no-interaction; fi && php -S 0.0.0.0:8080 -t public"]
