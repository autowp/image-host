FROM alpine

LABEL maintainer "dmitry@pereslegin.ru"

WORKDIR /app

VOLUME /var/log
VOLUME /public/image

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN apk update && apk upgrade && \ 
    apk add \
        bash \
        ca-certificates \
        git \
        nginx \
        php7 \
        php7-curl \
        php7-dom \
        php7-exif \
        php7-fileinfo \
        php7-fpm \
        php7-iconv \
        php7-imagick \
        php7-intl \
        php7-json \
        php7-gd \
        php7-mbstring \
        php7-opcache \
        php7-pdo \
        php7-pdo_mysql \
        php7-pdo_pgsql \
        php7-phar \
        php7-simplexml \
        php7-tokenizer \
        php7-xml \
        php7-xmlwriter \
        php7-zlib \
        py-pip \
        python \
    && \
    apk add php7-xdebug --repository http://dl-3.alpinelinux.org/alpine/edge/testing/ \
    && \
    pip install supervisor \
    && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --quiet && \
    rm composer-setup.php
#    && \
#    rm /etc/php/7.0/cli/conf.d/20-xdebug.ini && \
#    rm /etc/php/7.0/fpm/conf.d/20-xdebug.ini
    
COPY ./etc/ /etc/

COPY composer.json /app/composer.json
RUN php ./composer.phar install --no-progress --no-interaction --no-suggest --optimize-autoloader && \
    php ./composer.phar clearcache

ADD . /app

RUN chmod +x zf && \
    chmod +x start.sh && \
    chmod +x wait-for-it.sh

EXPOSE 80

CMD ["./start.sh"]
