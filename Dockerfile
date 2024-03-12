FROM docker.io/bitnami/php-fpm:8.2

# Copy configuration
COPY ./docker/php/php-fpm.conf /opt/bitnami/php/etc/php-fpm.conf
COPY ./docker/php/custom.ini /opt/bitnami/php/etc/conf.d/custom.ini

WORKDIR /app

EXPOSE 8000