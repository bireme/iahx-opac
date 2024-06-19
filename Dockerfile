FROM docker.io/bitnami/php-fpm:8.2 as builder

# Install build packages
RUN apt update -y && apt install -y autoconf build-essential

# Install PHP exstensions
ENV EXTENSION_DIR="/opt/bitnami/php/lib/php/extensions"
RUN pecl channel-update pecl.php.net
RUN pecl config-set ext_dir $EXTENSION_DIR \
       && pear config-set ext_dir $EXTENSION_DIR

RUN pecl install redis

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash


##########################################################################
FROM docker.io/bitnami/php-fpm:8.2

# Copy extensions from builder stage
COPY --from=builder \
    /opt/bitnami/php/lib/php/extensions/redis.so \
    /opt/bitnami/php/lib/php/extensions/

ENV PHP_INI_SCAN_DIR /opt/bitnami/php/lib/inc

# Copy Symfony CLI from builder stage
COPY --from=builder /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Copy composer binary to the image
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy dependencies control files
COPY composer.json composer.lock /app

# Change to app directory
WORKDIR /app

# Install project dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --optimize-autoloader --no-scripts

# Copy custom PHP/NGINX configurations
COPY ./docker/php/php-fpm.conf /opt/bitnami/php/etc/php-fpm.conf
COPY ./docker/php/php.ini-production /opt/bitnami/php/etc/php.ini

# Copy project files
COPY . /app

# Compile project assets
RUN php bin/console asset-map:compile

# Generate environment prod
RUN composer dump-env prod

# Change cache directory permissions
RUN chmod -R o+w /app/var/cache/

ARG DOCKER_TAG
ENV APP_VER=$DOCKER_TAG

EXPOSE 80