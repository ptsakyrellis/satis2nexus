FROM composer:latest
MAINTAINER Rectorat de Toulouse DSI-DEIL

# requires ext-gd * pour installer certains packages
RUN apk update && apk add libpng-dev --repository http://dl-3.alpinelinux.org/alpine/v3.8/main/
RUN docker-php-ext-install gd

# https://github.com/gliderlabs/docker-alpine/issues/30#issuecomment-372020089
RUN apk update \
    && apk upgrade \
    && apk add --no-cache \
        ca-certificates

# Add certif pour dépôt composer
COPY certs/composer.crt /usr/local/share/ca-certificates/composer.crt
RUN update-ca-certificates
# Test Certif
RUN openssl s_client -connect dsi-composer-services.in.ac-toulouse.fr:443