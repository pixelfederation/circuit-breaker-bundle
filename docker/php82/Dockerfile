FROM php:8.2-fpm

RUN apt-get update && apt-get install -y git-core zlib1g-dev libzip-dev zip unzip
RUN docker-php-ext-install zip

RUN pecl install xdebug apcu && \
    docker-php-ext-enable xdebug apcu

RUN echo 'apc.enable_cli=1' >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

RUN echo 'xdebug.file_link_format="phpstorm://open?url=file://%f&line=%%l"' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
        echo "xdebug.mode=develop" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
        echo "xdebug.max_nesting_level=10000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
        echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === trim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    mv composer.phar /usr/local/bin/composer && \
    unlink composer-setup.php

WORKDIR /srv/www
