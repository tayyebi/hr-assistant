FROM php:8.3-cli

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libgd-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip gd pdo_mysql calendar

WORKDIR /app

# Copy only the entrypoint script (code files will be mounted as volumes)
COPY ./scripts/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Default command: run entrypoint (installs deps, runs migrations/seed, starts server)
CMD ["/usr/local/bin/docker-entrypoint.sh"]
