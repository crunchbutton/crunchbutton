FROM php:5.6-apache

ENV DOCKER=1
#ENV DATABASE_URL=mysql://root:root@192.168.0.7:8889/crunchbutton
#ENV HEROKU=1


RUN apt-get update && apt-get install -y \
        libmcrypt-dev \
        php-pear \
        curl \
        git \
    && docker-php-ext-install iconv mcrypt \
	&& docker-php-ext-install mysql pdo_mysql



ADD conf/apache2.docker.conf /etc/apache2/sites-enabled/000-default.conf

# ADD deploy/id_rsa ~/.ssh/id_rsa
# ADD deploy/id_rsa.pub ~/.ssh/id_rsa.pub
# ADD deploy/id_rsa.pub ~/.ssh/known_hosts

# RUN chmod -R 0600 ~/.ssh/*

# RUN git clone git@github.com:crunchbutton/crunchbutton.git


ADD ./ /var/www/
RUN mkdir /var/www/logs
#RUN mkdir /var/www/cache
#RUN mkdir /var/www/cache/data
#RUN mkdir /var/www/cache/min
#RUN mkdir /var/www/cache/thumb

RUN a2enmod rewrite
RUN a2enmod headers