FROM php:8.3.8-apache
RUN docker-php-ext-install mysqli pdo_mysql