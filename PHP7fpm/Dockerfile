FROM php:7.2.1-fpm

RUN echo 'deb http://httpredir.debian.org/debian jessie contrib' >> /etc/apt/sources.list

RUN apt-get update
RUN export DEBIAN_FRONTEND=noninteractive && apt-get install -y --force-yes libssl-dev curl  libcurl4-gnutls-dev libxml2-dev libicu-dev libmcrypt4 libmemcached11 openssl

RUN docker-php-ext-install opcache

RUN pecl install apcu-5.1.5 && docker-php-ext-enable apcu

RUN docker-php-ext-install bcmath
RUN apt-get update
RUN apt-get install -y libmcrypt-dev curl git nginx zlib1g-dev wget libbz2-dev
RUN docker-php-ext-install bz2
RUN docker-php-ext-install mbstring
RUN apt-get install -y libpq-dev
RUN apt-get install -y libicu-dev
RUN docker-php-ext-install intl
RUN docker-php-ext-install iconv
RUN docker-php-ext-install pdo pdo_mysql 
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install zip
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng16-16
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install gd

CMD /var/app/cli/build.sh 

EXPOSE 9000
CMD ["php-fpm"]