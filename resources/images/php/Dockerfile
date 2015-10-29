FROM php:5.6.8-fpm
MAINTAINER Matt Glaman <nmd.matt@gmail.com>
# Install modules
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        libxml2-dev \
    && docker-php-ext-install mcrypt pdo_mysql mysql mysqli mbstring opcache soap \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd

# Make memcache available
RUN curl -L -o /root/memcache.tgz https://pecl.php.net/get/memcache-3.0.6.tgz && \
	cd /root && \
	tar -zxvf memcache.tgz && \
	cd /root/memcache-3.0.6 && \
	/usr/local/bin/phpize && \
	./configure --with-php-config=/usr/local/bin/php-config && \
	make  && \
	make install && \
	cd /root && \
	rm -fr /root/memcache-3.0.6 && \
	rm -fr /root/memcache.tgz

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

# Setup xhprof
RUN curl -L -o /root/xhprof.tgz https://pecl.php.net/get/xhprof-0.9.4.tgz && \
    cd /root && \
    tar -zxvf xhprof.tgz && \
    cd /root/xhprof-0.9.4/extension && \
    /usr/local/bin/phpize && \
    ./configure --with-php-config=/usr/local/bin/php-config && \
    make  && \
    make install && \
    cd /root && \
    rm -fr /root/xhprof-0.9.4.tgz && \
    rm -fr /root/xhprof.tgz

CMD ["php-fpm"]
