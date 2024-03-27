FROM docker.io/bitnami/php-fpm:8.2

# Install local git
RUN apt update -y && apt install -y git

# Copy custom PHP/NGINX configurations
#COPY ./docker/php/environment.conf /opt/bitnami/php/etc/environment.conf
COPY ./docker/php/php-fpm.conf /opt/bitnami/php/etc/php-fpm.conf
COPY ./docker/php/custom.ini /opt/bitnami/php/etc/conf.d/custom.ini

# Copy project files
COPY . /app

# Change to app directory
WORKDIR /app

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Copy composer binary to the image
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install project dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --optimize-autoloader

# Compile project assets
RUN php bin/console asset-map:compile

# Generate environment prod
RUN composer dump-env prod

EXPOSE 80