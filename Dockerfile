FROM php:8.2-apache
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo mbstring pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
