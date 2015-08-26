FROM php:5.6.8-fpm
# Install modules
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
    && docker-php-ext-install mcrypt pdo_mysql mysql mysqli mbstring opcache \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd
# Setup xdebug
RUN cd /tmp/ && \
    curl -O http://pecl.php.net/get/xdebug-2.3.2.tgz && \
    tar zxvf xdebug-2.3.2.tgz && \
    mv xdebug-2.3.2 /usr/src/php/ext/xdebug

# Setup xhprof
RUN cd /tmp/ && \
    curl -O https://pecl.php.net/get/xhprof-0.9.4.tgz && \
    tar zxvf xhprof-0.9.4.tgz && \
    mv xhprof-0.9.4/extension /usr/src/php/ext/xhprof
CMD ["php-fpm"]
