###### BASE STAGE ######
FROM docker.io/bitnami/php-fpm:8.2 AS base

# Copy configuration
COPY ./docker/php/php-fpm.conf /opt/bitnami/php/etc/php-fpm.conf
COPY ./docker/php/custom.ini /opt/bitnami/php/etc/conf.d/custom.ini

WORKDIR /app


###### DEVELOPMENT STAGE ######
FROM base AS dev

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

EXPOSE 8000

ENTRYPOINT ["symfony", "server:start"]
