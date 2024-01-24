FROM dockerhub.wdcloud.cc/php7.3-fpm:latest

COPY . /var/www/html/rd
WORKDIR /var/www/html/rd

RUN rm /etc/nginx/sites-enabled/default

COPY ./deploy/system/nginx.conf /etc/nginx/
COPY ./deploy/system/php.ini /usr/local/etc/php
COPY ./deploy/system/php-fpm.conf /usr/local/etc/
COPY ./deploy/deploy.conf /etc/nginx/conf.d/default.conf
COPY ./deploy/deploy_fe.conf /etc/nginx/conf.d/fe.conf

RUN useradd -d /var/www/html/rd -m bae && useradd www -s /bin/false -M

RUN chown -R bae:www /var/www/html/rd/storage

EXPOSE 80