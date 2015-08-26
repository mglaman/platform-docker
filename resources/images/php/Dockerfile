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
RUN curl -L -o /root/xdebug.tgz https://pecl.php.net/get/xdebug-2.3.2.tgz && \
			cd /root && \
			tar -zxvf xdebug.tgz && \
			cd /root/xdebug-2.3.2 && \
			/usr/local/bin/phpize && \
			./configure --enable-xdebug --with-php-config=/usr/local/bin/php-config && \
			make  && \
			make install && \
			cd /root && \
			rm -fr /root/xdebug-2.3.2 && \
			rm -fr /root/xdebug.tgz
CMD ["php-fpm"]
