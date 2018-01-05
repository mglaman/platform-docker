FROM php:7.1-fpm
MAINTAINER Matt Glaman <nmd.matt@gmail.com>
# Install modules
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxml2-dev
RUN docker-php-ext-install mcrypt pdo_mysql mysqli mbstring opcache soap bcmath
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd

# Make memcache available
#RUN curl -L -o /root/memcache.tgz https://pecl.php.net/get/memcache-3.0.6.tgz && \
#	cd /root && \
#	tar -zxvf memcache.tgz && \
#	cd /root/memcache-3.0.6 && \
#	/usr/local/bin/phpize && \
#	./configure --with-php-config=/usr/local/bin/php-config && \
#	make  && \
#	make install && \
#	cd /root && \
#	rm -fr /root/memcache-3.0.6 && \
#	rm -fr /root/memcache.tgz

# Setup xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Setup redis
RUN pecl install redis && docker-php-ext-enable redis

# Install APCu and APC backward compatibility
RUN pecl install apcu \
    && pecl install apcu_bc-1.0.3 \
    && docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini \
    && docker-php-ext-enable apc --ini-name 20-docker-php-ext-apc.ini

# XHPROF is not available in PHP 7.

RUN export VERSION=`php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;"` \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/${VERSION} \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so `php -r "echo ini_get('extension_dir');"`/blackfire.so \
    && echo "extension=blackfire.so\nblackfire.agent_socket=\${BLACKFIRE_PORT}" > $PHP_INI_DIR/conf.d/blackfire.ini

CMD ["php-fpm"]
