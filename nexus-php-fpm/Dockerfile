FROM php:7.0-fpm-alpine

ENV IMAGE_VERSION 2.3
ENV COMPOSER_VERSION 1.6.5

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
  apk add --no-cache --update --virtual .build-dependencies alpine-sdk build-base autoconf

RUN cp /usr/share/zoneinfo/Europe/Helsinki /etc/localtime
RUN docker-php-source extract && \
  pecl install xdebug && \
  docker-php-ext-enable xdebug  && \
  docker-php-ext-install calendar curl gettext bcmath mcrypt mysqli pdo_mysql shmop zip bz2 sockets wddx mbstring opcache pcntl xml xsl && \
  docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install gd && \
  docker-php-ext-configure ldap --with-libdir=lib/ && docker-php-ext-install ldap && \
  docker-php-source delete && \

# Build/add REDIS
RUN pecl install redis \
	 && docker-php-ext-enable redis

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

RUN chown www-data:devel /var/www/installations && \
    chown -R www-data:devel /var/www/boot && \
    chmod g+w /var/www/installations && \
    chmod -R g+w /var/www/boot

RUN sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /usr/local/etc/php-fpm.conf
RUN sed -i -e "s/max_children = 5/max_children = 50/g" /usr/local/etc/php-fpm.d/www.conf
ADD files/php.ini /usr/local/etc/php
ADD files/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d
ADD files/docker-php-ext-redis.ini /usr/local/etc/php/conf.d

# Memory Limit
RUN echo "memory_limit=-1" > $PHP_INI_DIR/conf.d/memory-limit.ini && \
    echo "date.timezone=${PHP_TIMEZONE:-UTC}" > $PHP_INI_DIR/conf.d/date_timezone.ini

# Setup the Composer installer, install Composer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
  && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
  && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }"
 
RUN php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --version=${COMPOSER_VERSION} && rm -rf /tmp/composer-setup.php

# Pre-cache composer files
RUN mkdir /tmp/cache
COPY files/composer/* /tmp/cache/
RUN cd /tmp/cache ; composer --prefer-dist --dev --verbose --optimize-autoloader install

# Add php boot files
ADD files/nexus-boot.tar.gz /var/www/boot

RUN apk del .build-dependencies
RUN rm -rf /tmp/cache
