# Use the same base image as nibrev/php-5.3-apache
FROM nibrev/php-5.3-apache

RUN docker-php-ext-install mysql
RUN a2enmod headers rewrite expires deflate filter setenvif include

