FROM webdevops/php-nginx:8.0

COPY . /var/www/html/api

WORKDIR /var/www/html

COPY vhost.conf /opt/docker/etc/nginx/vhost.conf

RUN chmod -R 777 /var/www/html/api/storage

EXPOSE 80
