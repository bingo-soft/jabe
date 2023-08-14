FROM php:8.2-cli

ENV PHP_VERSION=8.1
ENV TZ=Europe/Moscow
ENV SWOOLE_VERSION=4.8.13

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update
RUN apt-get install -y --no-install-recommends \
    unzip \
    gcc \
    libpcre3-dev \
    zlib1g-dev \
    libfreetype6-dev \
    openssl \
    libssl-dev \
    wget \
    libpq-dev \
    git \
    libzip-dev \
    libxml2-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    make automake autoconf && \
    docker-php-ext-install iconv zip gd pdo_pgsql pdo_mysql soap \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache

# PHP-PSR
RUN git clone https://github.com/jbboehr/php-psr.git /root/psr && \
    cd /root/psr && \
    phpize && \
    ./configure && \
    make && \
    make test && \
    make install && \
    echo "extension=psr.so" > /usr/local/etc/php/conf.d/29-psr.ini && \
    cd && rm -Rf /root/psr

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Install Kafka module
RUN mkdir -p $HOME/librdkafka && \
    cd $HOME/librdkafka && \
    git clone https://github.com/edenhill/librdkafka.git . && \
    git checkout v1.6.2 && \
    ./configure && make && make install && \
    pecl install rdkafka && \
    echo "extension=rdkafka.so" > /usr/local/etc/php/conf.d/10-rdkafka.ini

# Install Swoole module
RUN cd /tmp && wget https://pecl.php.net/get/swoole-${SWOOLE_VERSION}.tgz && \
    tar zxvf swoole-${SWOOLE_VERSION}.tgz && \
    cd swoole-${SWOOLE_VERSION}  && \
    phpize  && \
    ./configure --enable-coroutine-postgresql && \
    make && make install && \
    echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini

COPY php.ini /usr/local/etc/php/php.ini
COPY src /usr/src/engine
COPY composer.json /usr/src/composer.json
COPY composer.lock /usr/src/composer.lock

# Install dependencies
#RUN cd /usr/src/ && composer update --no-dev
COPY vendor /usr/src/vendor
WORKDIR /usr/src/engine
CMD [ "php", "./index.php" ]