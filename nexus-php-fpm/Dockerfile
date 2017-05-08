FROM php:7.0-fpm-alpine

ENV IMAGE_VERSION 1.0
# Register the COMPOSER_HOME environment variable
ENV COMPOSER_HOME /var/www/installations/nexus/lib
ENV COMPOSER_VERSION 1.2.0

# Add global binary directory to PATH and make sure to re-export it
ENV PATH /composer/vendor/bin:$PATH

# Allow Composer to be run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apk add --no-cache --update \
  git \
  subversion \
  curl-dev \
  freetype-dev \
  gettext-dev \
  libjpeg-turbo-dev \
  libmcrypt-dev \
  libpng-dev \
  libbz2 \
  bzip2-dev \
  libstdc++ \
  libxslt-dev \
  openldap-dev \
  make \
  unzip \
  bash \
  tzdata \
  wget && \
  apk add --no-cache --update --virtual .build-dependencies alpine-sdk build-base

RUN cp /usr/share/zoneinfo/Europe/Helsinki /etc/localtime

RUN docker-php-ext-install calendar curl gettext bcmath mcrypt mysqli pdo_mysql shmop zip bz2 sockets wddx mbstring opcache pcntl xml xsl && \
  docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && \
  docker-php-ext-install gd && \
  docker-php-ext-configure ldap --with-libdir=lib/ && \
  docker-php-ext-install ldap

# Add enkorascripts user
RUN addgroup devel && \
    addgroup enkorascripts && \
    adduser -D -S -h /home/enkorascripts -s /bin/bash -G www-data -G devel -G enkorascripts enkorascripts && \
    echo enkorascripts:password | chpasswd && \
    mkdir -p /home/enkorascripts/.ssh

# Installations folder
RUN mkdir -p /var/www/installations && \
    mkdir -p /var/www/boot && \
    mkdir -p /var/www/installations/nexus/lib && \
    rm -rf /var/www/html

COPY files/composer.json files/composer.lock /var/www/installations/nexus/lib/

RUN chown www-data:devel /var/www/installations && \
    chown -R www-data:devel /var/www/boot && \
    chmod g+w /var/www/installations && \
    chmod -R g+w /var/www/boot

RUN sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /usr/local/etc/php-fpm.conf
ADD files/php.ini /usr/local/etc/php

# Memory Limit
RUN echo "memory_limit=-1" > $PHP_INI_DIR/conf.d/memory-limit.ini && \
    echo "date.timezone=${PHP_TIMEZONE:-UTC}" > $PHP_INI_DIR/conf.d/date_timezone.ini

# Setup the Composer installer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
  && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
  && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }"

# Install Composer
RUN php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --version=${COMPOSER_VERSION} && rm -rf /tmp/composer-setup.php

# Display version information.
RUN composer --version

WORKDIR /var/www/installations/nexus/lib
RUN composer --dev --verbose --optimize-autoloader install

RUN apk del .build-dependencies