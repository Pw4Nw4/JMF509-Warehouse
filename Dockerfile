FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
