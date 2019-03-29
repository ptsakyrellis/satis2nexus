FROM php:7.2-fpm
MAINTAINER Rectorat de Toulouse DSI-DEIL
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update
# Warning sur apt-utils
RUN apt-get install -y --no-install-recommends apt-utils
RUN apt-get install -y libfreetype6-dev
RUN apt-get install -y libjpeg62-turbo-dev
RUN apt-get install -y libmcrypt-dev
RUN apt-get install -y libpng-dev
RUN apt-get install -y default-libmysqlclient-dev
RUN apt-get install -y libbz2-dev
RUN apt-get install -y libzip-dev
RUN apt-get install -y zip
RUN apt-get install -y curl
RUN apt-get install -y libldap2-dev
RUN apt-get install -y zlib1g-dev
RUN apt-get install -y libicu-dev
RUN apt-get install -y libxml2-dev
RUN docker-php-ext-install bcmath bz2 calendar iconv mbstring opcache pdo_mysql
RUN docker-php-ext-configure zip --with-libzip
RUN docker-php-ext-install zip
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
RUN docker-php-ext-install ldap
RUN docker-php-ext-install gd

# XDEBUG
RUN pecl install xdebug-2.6.1
RUN docker-php-ext-enable xdebug
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_autostart=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.remote_host=docker.for.win.localhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# APCU
RUN pecl install apcu
RUN docker-php-ext-enable apcu

# Certif openssl pour matomo => crt obligatoire créé sous linux
COPY certs/rectorat.crt /usr/local/share/ca-certificates/rectorat.crt
RUN update-ca-certificates
# Test Certif
RUN openssl s_client -connect analyse-outils-da.ac-toulouse.fr:443

# COMPOSER
RUN curl -sS https://getcomposer.org/installer -o ~/composer-setup.php
RUN php ~/composer-setup.php --install-dir=/usr/local/bin --filename=composer

RUN echo 'alias sf="php app/console"' >> ~/.bashrc
RUN echo 'alias sf3="php bin/console"' >> ~/.bashrc