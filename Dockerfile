FROM php:8.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    libpq-dev \
    curl \
    gnupg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

WORKDIR /var/www/html

RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

# Verify installations
RUN node --version && npm --version

EXPOSE 8080

# Change Apache configuration to listen on port 8080
RUN sed -i 's/80/8080/' /etc/apache2/ports.conf
RUN sed -i 's/80/8080/' /etc/apache2/sites-available/000-default.conf
