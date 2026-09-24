FROM php:8.4-apache

RUN docker-php-ext-install mysqli pdo_mysql

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]