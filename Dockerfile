FROM webdevops/php-nginx:8.0-alpine

COPY . /var/www/html/api

WORKDIR /var/www/html

COPY vhost.conf /opt/docker/etc/nginx/vhost.conf

RUN chmod -R 777 /var/www/html/api/storage

EXPOSE 80

#docker build -t laravel .
#docker run --name laravel  -p 8083:80 -d laravel
#docker exec -it laravel /bin/bash
#docker stop laravel && docker rm laravel && docker rmi laravel
